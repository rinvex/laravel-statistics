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
        Schema::create(config('rinvex.statistics.tables.data'), function (Blueprint $table) {
            // Columns
            $table->increments('id');
            $table->string('session_id');
            $table->nullableMorphs('user');
            $table->integer('status_code');
            $table->text('uri');
            $table->string('method');
            $table->json('server');
            $table->json('input')->nullable();
            $table->timestamp('created_at')->nullable();
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
}
