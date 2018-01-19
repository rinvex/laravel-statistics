<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatisticsRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get users model
        $userModel = config('auth.providers.'.config('auth.guards.'.config('auth.defaults.guard').'.provider').'.model');

        Schema::create(config('rinvex.statistics.tables.requests'), function (Blueprint $table) use ($userModel) {
            // Columns
            $table->increments('id');
            $table->integer('route_id')->unsigned();
            $table->integer('agent_id')->unsigned();
            $table->integer('device_id')->unsigned();
            $table->integer('platform_id')->unsigned();
            $table->integer('path_id')->unsigned();
            $table->integer('geoip_id')->unsigned();
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('session_id');
            $table->string('method');
            $table->integer('status_code');
            $table->string('protocol_version')->nullable();
            $table->text('referer')->nullable();
            $table->string('language');
            $table->boolean('is_no_cache')->default(0);
            $table->boolean('wants_json')->default(0);
            $table->boolean('is_secure')->default(0);
            $table->boolean('is_json')->default(0);
            $table->boolean('is_ajax')->default(0);
            $table->boolean('is_pjax')->default(0);
            $table->timestamp('created_at')->nullable();

            // Indexes
            $table->foreign('route_id')->references('id')->on(config('rinvex.statistics.tables.routes'))
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('agent_id')->references('id')->on(config('rinvex.statistics.tables.agents'))
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('device_id')->references('id')->on(config('rinvex.statistics.tables.devices'))
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('platform_id')->references('id')->on(config('rinvex.statistics.tables.platforms'))
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('path_id')->references('id')->on(config('rinvex.statistics.tables.paths'))
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('geoip_id')->references('id')->on(config('rinvex.statistics.tables.geoips'))
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on((new $userModel())->getTable())
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('rinvex.statistics.tables.requests'));
    }

    /**
     * Get jsonable column data type.
     *
     * @return string
     */
    protected function jsonable()
    {
        return DB::connection()->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql'
               && version_compare(DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION), '5.7.8', 'ge')
            ? 'json' : 'text';
    }
}
