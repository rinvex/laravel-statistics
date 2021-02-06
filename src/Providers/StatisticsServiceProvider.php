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
        $this->registerModels([
            'rinvex.statistics.datum' => Datum::class,
            'rinvex.statistics.request' => Request::class,
            'rinvex.statistics.agent' => Agent::class,
            'rinvex.statistics.geoip' => Geoip::class,
            'rinvex.statistics.route' => Route::class,
            'rinvex.statistics.device' => Device::class,
            'rinvex.statistics.platform' => Platform::class,
            'rinvex.statistics.path' => Path::class,
        ]);

        // Register console commands
        $this->registerCommands($this->commands);
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
