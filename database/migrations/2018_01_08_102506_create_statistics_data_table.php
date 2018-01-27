<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatisticsDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Get users model
        $userModel = config('auth.providers.'.config('auth.guards.'.config('auth.defaults.guard').'.provider').'.model');

        Schema::create(config('rinvex.statistics.tables.data'), function (Blueprint $table) use ($userModel) {
            // Columns
            $table->increments('id');
            $table->string('session_id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('status_code');
            $table->text('uri');
            $table->string('method');
            $table->{$this->jsonable()}('server');
            $table->{$this->jsonable()}('input')->nullable();
            $table->timestamp('created_at')->nullable();

            // Indexes
            $table->foreign('user_id')->references('id')->on((new $userModel())->getTable())
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('rinvex.statistics.tables.data'));
    }

    /**
     * Get jsonable column data type.
     *
     * @return string
     */
    protected function jsonable(): string
    {
        return DB::connection()->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql'
               && version_compare(DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION), '5.7.8', 'ge')
            ? 'json' : 'text';
    }
}
