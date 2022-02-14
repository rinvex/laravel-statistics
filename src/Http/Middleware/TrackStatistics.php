<?php

declare(strict_types=1);

namespace Rinvex\Statistics\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Rinvex\Statistics\Jobs\CrunchStatistics;
use Rinvex\Statistics\Jobs\CleanStatisticsRequests;

class TrackStatistics
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    /**
     * Perform any final actions for the request lifecycle.
     *
     * @param \Illuminate\Http\Request                   $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     *
     * @return void
     */
    public function terminate($request, $response): void
    {
        $currentUser = $request->user();

        app('rinvex.statistics.datum')->fill([
            'session_id' => $request->session()->getId(),
            'user_id' => $currentUser?->getKey(),
            'user_type' => $currentUser?->getMorphClass(),
            'status_code' => $response->getStatusCode(),
            'uri' => $request->getUri(),
            'method' => $request->getMethod(),
            'server' => $request->server() ?: null,
            'input' => $request->input() ? $request->except(config('rinvex.statistics.exclude_input_fields')) : null,
            'created_at' => Carbon::now(),
        ])->save();

        // Here we will see if this request hits the statistics crunching lottery by hitting
        // the odds needed to perform statistics crunching on any given request. If we do
        // hit it, we'll call this handler to let it crunch numbers and the hard work.
        if ($this->configHitsLottery()) {
            CrunchStatistics::dispatch();

            // Now let's do some garbage collection and clean old statistics requests
            CleanStatisticsRequests::dispatch();
        }
    }

    /**
     * Determine if the configuration odds hit the lottery.
     *
     * @return bool
     */
    protected function configHitsLottery(): bool
    {
        $config = config('rinvex.statistics.lottery');

        return $config ? random_int(1, $config[1]) <= $config[0] : false;
    }
}
