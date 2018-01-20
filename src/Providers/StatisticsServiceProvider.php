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
        $this->app->alias('rinvex.statistics.datum', Datum::class);

        $this->app->singleton('rinvex.statistics.request', function ($app) {
            return new $app['config']['rinvex.statistics.models.request']();
        });
        $this->app->alias('rinvex.statistics.request', Request::class);

        $this->app->singleton('rinvex.statistics.agent', function ($app) {
            return new $app['config']['rinvex.statistics.models.agent']();
        });
        $this->app->alias('rinvex.statistics.agent', Agent::class);

        $this->app->singleton('rinvex.statistics.geoip', function ($app) {
            return new $app['config']['rinvex.statistics.models.geoip']();
        });
        $this->app->alias('rinvex.statistics.geoip', Geoip::class);

        $this->app->singleton('rinvex.statistics.route', function ($app) {
            return new $app['config']['rinvex.statistics.models.route']();
        });
        $this->app->alias('rinvex.statistics.route', Route::class);

        $this->app->singleton('rinvex.statistics.device', function ($app) {
            return new $app['config']['rinvex.statistics.models.device']();
        });
        $this->app->alias('rinvex.statistics.device', Device::class);

        $this->app->singleton('rinvex.statistics.platform', function ($app) {
            return new $app['config']['rinvex.statistics.models.platform']();
        });
        $this->app->alias('rinvex.statistics.platform', Platform::class);

        $this->app->singleton('rinvex.statistics.path', function ($app) {
            return new $app['config']['rinvex.statistics.models.path']();
        });
        $this->app->alias('rinvex.statistics.path', Path::class);

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
