<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterColumnChosenOverlayInInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `inventory` CHANGE `chosen_overlay` `chosen_overlay` varchar(255) COLLATE 'utf8_general_ci' NULL DEFAULT '';");
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE inventory MODIFY COLUMN chosen_overlay VARCHAR(255) NOT NULL DEFAULT '';");
    }
}