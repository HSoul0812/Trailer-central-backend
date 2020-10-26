<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Parts\PartOrder;

class CreatePartOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('part_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('dealer_id');
            $table->integer('website_id');
            $table->string('shipto_name');
            $table->text('shipto_address');
            $table->enum('status', PartOrder::STATUS_FIELDS);
            $table->enum('fulfillment_type', PartOrder::FULFILLMENT_TYPES);
            $table->string('email_address');
            $table->string('phone_number', 20);
            $table->text('cart_items');
            $table->decimal('subtotal', 9, 2);
            $table->decimal('tax', 9, 2);
            $table->decimal('shipping', 9, 2);
            $table->string('order_key');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('part_orders');
    }
}
