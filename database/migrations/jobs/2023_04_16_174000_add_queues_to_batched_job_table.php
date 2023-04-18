<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Database\traits\WithMysqlServerVersion;

class AddQueuesToBatchedJobTable extends Migration
{
    use WithMysqlServerVersion;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('batched_job', function (Blueprint $table): void {
            $type = $this->version() < '5.7.0' ? 'text' : 'json';

            $table->{$type}('queues')
                ->nullable()
                ->after('batch_id')
                ->comment('a valid array of monitored queues');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('batched_job', static function (Blueprint $table): void {
            $table->dropColumn('queues');
        });
    }
}
