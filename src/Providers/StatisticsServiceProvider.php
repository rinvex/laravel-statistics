<?php

declare(strict_types=1);

namespace Rinvex\Statistics\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Rinvex\Statistics\Contracts\PathContract;
use Rinvex\Statistics\Contracts\AgentContract;
use Rinvex\Statistics\Contracts\DatumContract;
use Rinvex\Statistics\Contracts\GeoipContract;
use Rinvex\Statistics\Contracts\RouteContract;
use Rinvex\Statistics\Contracts\DeviceContract;
use Rinvex\Statistics\Contracts\RequestContract;
use Rinvex\Statistics\Contracts\PlatformContract;
use Rinvex\Statistics\Console\Commands\MigrateCommand;
use Rinvex\Statistics\Console\Commands\PublishCommand;
use Rinvex\Statistics\Http\Middleware\TrackStatistics;
use Rinvex\Statistics\Console\Commands\RollbackCommand;

class StatisticsServiceProvider extends ServiceProvider
{
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
        $this->app->singleton('rinvex.statistics.datum', function ($app) {
            return new $app['config']['rinvex.statistics.models.datum']();
        });
        $this->app->alias('rinvex.statistics.datum', DatumContract::class);

        $this->app->singleton('rinvex.statistics.request', function ($app) {
            return new $app['config']['rinvex.statistics.models.request']();
        });
        $this->app->alias('rinvex.statistics.request', RequestContract::class);

        $this->app->singleton('rinvex.statistics.agent', function ($app) {
            return new $app['config']['rinvex.statistics.models.agent']();
        });
        $this->app->alias('rinvex.statistics.agent', AgentContract::class);

        $this->app->singleton('rinvex.statistics.geoip', function ($app) {
            return new $app['config']['rinvex.statistics.models.geoip']();
        });
        $this->app->alias('rinvex.statistics.geoip', GeoipContract::class);

        $this->app->singleton('rinvex.statistics.route', function ($app) {
            return new $app['config']['rinvex.statistics.models.route']();
        });
        $this->app->alias('rinvex.statistics.route', RouteContract::class);

        $this->app->singleton('rinvex.statistics.device', function ($app) {
            return new $app['config']['rinvex.statistics.models.device']();
        });
        $this->app->alias('rinvex.statistics.device', DeviceContract::class);

        $this->app->singleton('rinvex.statistics.platform', function ($app) {
            return new $app['config']['rinvex.statistics.models.platform']();
        });
        $this->app->alias('rinvex.statistics.platform', PlatformContract::class);

        $this->app->singleton('rinvex.statistics.path', function ($app) {
            return new $app['config']['rinvex.statistics.models.path']();
        });
        $this->app->alias('rinvex.statistics.path', PathContract::class);

        // Register console commands
        ! $this->app->runningInConsole() || $this->registerCommands();
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Router $router)
    {
        // Load migrations
        ! $this->app->runningInConsole() || $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Publish Resources
        ! $this->app->runningInConsole() || $this->publishResources();

        // Push middleware to web group
        $router->pushMiddlewareToGroup('web', TrackStatistics::class);
    }

    /**
     * Publish resources.
     *
     * @return void
     */
    protected function publishResources(): void
    {
        $this->publishes([realpath(__DIR__.'/../../config/config.php') => config_path('rinvex.statistics.php')], 'rinvex-statistics-config');
        $this->publishes([realpath(__DIR__.'/../../database/migrations') => database_path('migrations')], 'rinvex-statistics-migrations');
    }

    /**
     * Register console commands.
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        // Register artisan commands
        foreach ($this->commands as $key => $value) {
            $this->app->singleton($value, function ($app) use ($key) {
                return new $key();
            });
        }

        $this->commands(array_values($this->commands));
    }
}
