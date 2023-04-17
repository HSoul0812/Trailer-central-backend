<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveGroupFromBatchedJobTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('batched_job', function (Blueprint $table): void {
            $table->dropColumn('group');
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
            $table->string('group', 38)->after('batch_id')->index('batched_job_group_index');
        });
    }
}
