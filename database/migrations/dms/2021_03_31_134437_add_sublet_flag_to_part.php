<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add is_sublet_specific boolean field to parts_v1 table.
 * 
 * It's used to identify a generic part for sublet items in repair orders page
 */
class AddSubletFlagToPart extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parts_v1', function (Blueprint $table) {
            $table->boolean('is_sublet_specific')->default(0);
        });
        Schema::table('dms_other_item', function (Blueprint $table) {
            $table->integer('po_id')->after('repair_order_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parts_v1', function (Blueprint $table) {
            $table->dropColumn('is_sublet_specific');
        });
        Schema::table('dms_other_item', function (Blueprint $table) {
            $table->dropColumn('po_id');
        });
    }
}
