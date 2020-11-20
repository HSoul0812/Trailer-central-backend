<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReceiptToPoInventory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_purchase_order_inventory', function (Blueprint $table) {
            $table->unsignedInteger('po_receipt_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_purchase_order_inventory', function (Blueprint $table) {
            $table->dropColumn('po_receipt_id');
        });
    }
}
