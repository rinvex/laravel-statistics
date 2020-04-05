<?php

declare(strict_types=1);

namespace Rinvex\Statistics\Providers;

use Illuminate\Routing\Router;
use Rinvex\Statistics\Models\Path;
use Rinvex\Statistics\Models\Agent;
use Rinvex\Statistics\Models\Datum;
use Rinvex\Statistics\Models\Geoip;
use Rinvex\Statistics\Models\Route;
use Rinvex\Statistics\Models\Device;
use Rinvex\Statistics\Models\Request;
use Rinvex\Statistics\Models\Platform;
use Illuminate\Support\ServiceProvider;
use Rinvex\Support\Traits\ConsoleTools;
use Rinvex\Statistics\Console\Commands\MigrateCommand;
use Rinvex\Statistics\Console\Commands\PublishCommand;
use Rinvex\Statistics\Http\Middleware\TrackStatistics;
use Rinvex\Statistics\Console\Commands\RollbackCommand;

class StatisticsServiceProvider extends ServiceProvider
{
    use ConsoleTools;

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        MigrateCommand::class => 'command.rinvex.statistics.migrate',
        PublishCommand::class => 'command.rinvex.statistics.publish',
        RollbackCommand::class => 'command.rinvex.statistics.rollback',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/config.php'), 'rinvex.statistics');

        // Bind eloquent models to IoC container
        $this->app->singleton('rinvex.statistics.datum', $datumModel = $this->app['config']['rinvex.statistics.models.datum']);
        $datumModel === Datum::class || $this->app->alias('rinvex.statistics.datum', Datum::class);

        $this->app->singleton('rinvex.statistics.request', $requestModel = $this->app['config']['rinvex.statistics.models.request']);
        $requestModel === Request::class || $this->app->alias('rinvex.statistics.request', Request::class);

        $this->app->singleton('rinvex.statistics.agent', $agentModel = $this->app['config']['rinvex.statistics.models.agent']);
        $agentModel === Agent::class || $this->app->alias('rinvex.statistics.agent', Agent::class);

        $this->app->singleton('rinvex.statistics.geoip', $geoipModel = $this->app['config']['rinvex.statistics.models.geoip']);
        $geoipModel === Geoip::class || $this->app->alias('rinvex.statistics.geoip', Geoip::class);

        $this->app->singleton('rinvex.statistics.route', $routeModel = $this->app['config']['rinvex.statistics.models.route']);
        $routeModel === Route::class || $this->app->alias('rinvex.statistics.route', Route::class);

        $this->app->singleton('rinvex.statistics.device', $deviceModel = $this->app['config']['rinvex.statistics.models.device']);
        $deviceModel === Device::class || $this->app->alias('rinvex.statistics.device', Device::class);

        $this->app->singleton('rinvex.statistics.platform', $platformModel = $this->app['config']['rinvex.statistics.models.platform']);
        $platformModel === Platform::class || $this->app->alias('rinvex.statistics.platform', Platform::class);

        $this->app->singleton('rinvex.statistics.path', $pathModel = $this->app['config']['rinvex.statistics.models.path']);
        $pathModel === Path::class || $this->app->alias('rinvex.statistics.path', Path::class);

        // Register console commands
        ! $this->app->runningInConsole() || $this->registerCommands();
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Router $router)
    {
        // Publish Resources
        $this->publishesConfig('rinvex/laravel-statistics');
        $this->publishesMigrations('rinvex/laravel-statistics');
        ! $this->autoloadMigrations('rinvex/laravel-statistics') || $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Push middleware to web group
        $router->pushMiddlewareToGroup('web', TrackStatistics::class);
    }
}
