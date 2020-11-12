<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDmsPurchaseOrderInventory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dms_purchase_order_inventory', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('purchase_order_id');
            $table->unsignedInteger('inventory_id');
            $table->decimal('act_cost', 10, 2);
            $table->unsignedInteger('qty');
            $table->unsignedInteger('received_qty')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dms_purchase_order_inventory');
    }
}
