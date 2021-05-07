<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatisticsPathsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('rinvex.statistics.tables.paths'), function (Blueprint $table) {
            // Columns
            $table->increments('id');
            $table->string('host');
            $table->string('locale');
            $table->string('path');
            $table->string('method');
            $table->json('parameters')->nullable();
            $table->integer('count')->unsigned()->default(0);

            // Indexes
            $table->unique(['host', 'path', 'method', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('rinvex.statistics.tables.paths'));
    }
}
