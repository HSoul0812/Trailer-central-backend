<?php

use Illuminate\Database\Migrations\Migration;

class ModifyBatchIdFromBatchedJobTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE batched_job MODIFY COLUMN batch_id varchar(76) NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE batched_job MODIFY COLUMN batch_id varchar(38) NOT NULL');
    }
}
