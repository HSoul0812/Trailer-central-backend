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
            $table->enum('status', PartOrder::STATUS_FIELDS)->index();
            $table->enum('fulfillment_type', PartOrder::FULFILLMENT_TYPES)->index();
            $table->string('email_address')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('shipto_name')->nullable();
            $table->text('shipto_address')->nullable();
            $table->text('cart_items');
            $table->decimal('subtotal', 9, 2);
            $table->decimal('tax', 9, 2);
            $table->decimal('shipping', 9, 2);
            $table->string('order_key')->nullable();
            $table->timestamps();

            $table->index(['dealer_id', 'website_id']);
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
