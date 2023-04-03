<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropQueueJobIdFromMonitoredJobTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('monitored_job', static function (Blueprint $table): void {
            $table->dropColumn('queue_job_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('monitored_job', static function (Blueprint $table): void {
            $table->string('queue_job_id', 38)
                ->after('queue')
                ->unique()
                ->nullable()
                ->comment('the queue job id belonging to this monitored job. It is unique');
        });
    }
}
