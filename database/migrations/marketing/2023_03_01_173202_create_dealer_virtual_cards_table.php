<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealerVirtualCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealer_virtual_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dealer_id')->index();
            $table->enum('type', VirtualCard::CARD_SERVICES)->index();
            $table->integer('card_number', 20);
            $table->integer('security', 5);
            $table->string('name_on_card', 100);
            $table->string('address_street', 100);
            $table->string('address_city', 50);
            $table->string('address_state', 5);
            $table->string('address_zip', 20);
            $table->date('expires_at')->index();
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
        Schema::dropIfExists('dealer_virtual_cards');
    }
}
