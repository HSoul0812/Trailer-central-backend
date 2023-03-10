<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchedJobTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('batched_job', static function (Blueprint $table) {
            $table->string('batch_id', 38)->primary();
            $table->string('group', 38)->index('batched_job_group_index');

            $table->integer('total_jobs')->unsigned();

            $table->integer('processed_jobs')->unsigned();

            $table->integer('failed_jobs')->unsigned();


            $table->timestamp('created_at')->useCurrent()->comment('when the job was created');
            $table->timestamp('updated_at')->nullable()->comment('when last update happened to the job');
            $table->timestamp('finished_at')->nullable()->comment('when the job was finished (or failed)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('batched_job');
    }
}
