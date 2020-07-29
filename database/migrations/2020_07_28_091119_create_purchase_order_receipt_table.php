<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderReceiptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dms_purchase_order_receipt', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('purchase_order_id');
            $table->string('ref_num');
            $table->text('memo')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Add po_receipt_id to dms_purchase_order_parts_received table
        Schema::table('dms_purchase_order_parts_received', function (Blueprint $table) {
            $table->integer('po_receipt_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dms_purchase_order_receipt');

        Schema::table('dms_purchase_order_parts_received', function (Blueprint $table) {
            $table->dropColumn('po_receipt_id');
        });
    }
}
