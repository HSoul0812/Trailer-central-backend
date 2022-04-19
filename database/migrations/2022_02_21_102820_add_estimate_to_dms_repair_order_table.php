<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstimateToDmsRepairOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_repair_order', function (Blueprint $table) {
            DB::statement("ALTER TABLE dms_repair_order MODIFY COLUMN status ENUM('picked_up','ready_for_pickup','on_tech_clipboard','waiting_custom','waiting_parts','warranty_processing','quote','work_available', 'closed_quote', 'estimate')");     
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_repair_order', function (Blueprint $table) {
            DB::statement("ALTER TABLE dms_repair_order MODIFY COLUMN status ENUM('picked_up','ready_for_pickup','on_tech_clipboard','waiting_custom','waiting_parts','warranty_processing','quote','work_available', 'closed_quote')");
        });
    }
}