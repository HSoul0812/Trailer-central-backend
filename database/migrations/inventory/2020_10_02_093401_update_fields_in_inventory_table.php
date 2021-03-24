<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateFieldsInInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE inventory MODIFY COLUMN chosen_overlay VARCHAR(255) NOT NULL DEFAULT "";');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE inventory MODIFY COLUMN chosen_overlay VARCHAR(255) NOT NULL;');
    }
}
