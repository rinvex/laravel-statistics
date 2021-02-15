<?php

declare(strict_types=1);

namespace Rinvex\Statistics\Jobs;

use Exception;
use UAParser\Parser;
use Jenssegers\Agent\Agent;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request as LaravelRequest;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class CrunchStatistics implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('rinvex.statistics.datum')->each(function ($item) {
            try {
                $symfonyRequest = SymfonyRequest::create($item['uri'], $item['server']['REQUEST_METHOD'], $item['input'] ?? [], [], [], $item['server']);
                $symfonyRequest->overrideGlobals();

                LaravelRequest::enableHttpMethodParameterOverride();
                $laravelRequest = LaravelRequest::createFromBase($symfonyRequest);
                $laravelRoute = app('router')->getRoutes()->match($laravelRequest);
                $laravelRequest->setRouteResolver(function () use ($laravelRoute) {
                    return $laravelRoute;
                });

                $tokens = [];
                $agent = new Agent($item['server']);
                $UAParser = Parser::create()->parse($agent->getUserAgent());
                $kind = $agent->isDesktop() ? 'desktop' : ($agent->isTablet() ? 'tablet' : ($agent->isPhone() ? 'phone' : ($agent->isRobot() ? 'robot' : 'unknown')));

                collect($laravelRequest->route()->getCompiled()->getTokens())->map(function ($item) use (&$tokens) {
                    return ($item = collect($item)) && $item->contains('variable') ? $tokens[$item[3]] = $item[2] : null;
                });

                $route = app('rinvex.statistics.route')->firstOrCreate([
                    'name' => $laravelRoute->getName() ?: $laravelRoute->uri(),
                ], [
                    'path' => $laravelRoute->uri(),
                    'action' => $laravelRoute->getActionName(),
                    'middleware' => $laravelRoute->gatherMiddleware() ?: null,
                    'parameters' => $tokens ?: null,
                ]);

                $agent = app('rinvex.statistics.agent')->firstOrCreate([
                    'name' => $agent->getUserAgent(),
                    'kind' => $kind,
                    'family' => $UAParser->ua->family,
                    'version' => $UAParser->ua->toVersion(),
                ]);

                $device = app('rinvex.statistics.device')->firstOrCreate([
                    'family' => $UAParser->device->family,
                    'model' => $UAParser->device->model,
                    'brand' => $UAParser->device->brand,
                ]);

                $platform = app('rinvex.statistics.platform')->firstOrCreate([
                    'family' => $UAParser->os->family,
                    'version' => $UAParser->os->toVersion(),
                ]);

                $path = app('rinvex.statistics.path')->firstOrCreate([
                    'host' => $laravelRequest->getHost(),
                    'path' => $laravelRequest->decodedPath(),
                    'method' => $laravelRequest->getMethod(),
                    'locale' => $laravelRequest->route('locale') ?? app()->getLocale(),
                ], [
                    'accessarea' => $laravelRequest->input('accessarea'),
                    'parameters' => $laravelRoute->parameters() ?: null,
                ]);

                $geoip = app('rinvex.statistics.geoip')->firstOrCreate([
                    'client_ip' => $ip = $laravelRequest->getClientIp(),
                    'latitude' => geoip($ip)->getAttribute('lat'),
                    'longitude' => geoip($ip)->getAttribute('lon'),
                ], [
                    'client_ips' => $laravelRequest->getClientIps() ?: null,
                    'country_code' => mb_strtoupper(geoip($ip)->getAttribute('iso_code')),
                    'is_from_trusted_proxy' => $laravelRequest->isFromTrustedProxy(),
                    'division_code' => geoip($ip)->getAttribute('state'),
                    'postal_code' => geoip($ip)->getAttribute('postal_code'),
                    'timezone' => geoip($ip)->getAttribute('timezone'),
                    'city' => geoip($ip)->getAttribute('city'),
                ]);

                $requestDetails = [
                    'route_id' => $route->getKey(),
                    'agent_id' => $agent->getKey(),
                    'device_id' => $device->getKey(),
                    'platform_id' => $platform->getKey(),
                    'path_id' => $path->getKey(),
                    'geoip_id' => $geoip->getKey(),
                    'user_id' => $item['user_id'],
                    'user_type' => $item['user_type'],
                    'session_id' => $item['session_id'],
                    'status_code' => $item['status_code'],
                    'referer' => $laravelRequest->header('referer') ?: $laravelRequest->input('utm_source'),
                    'protocol_version' => $laravelRequest->getProtocolVersion(),
                    'language' => $laravelRequest->getPreferredLanguage(),
                    'is_no_cache' => $laravelRequest->isNoCache(),
                    'wants_json' => $laravelRequest->wantsJson(),
                    'is_secure' => $laravelRequest->isSecure(),
                    'is_json' => $laravelRequest->isJson(),
                    'is_ajax' => $laravelRequest->ajax(),
                    'is_pjax' => $laravelRequest->pjax(),
                    'created_at' => $item['created_at'],
                ];

                app('rinvex.statistics.request')->create($requestDetails);
                $item->delete();
            } catch (Exception $exception) {
            }
        });
    }
}
