<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableDealerRefundsItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealer_refunds_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('id'); // int(11) unsigned NOT NULL AUTO_INCREMENT,
            $table->integer('dealer_refunds_id', false, true); //' int(10) unsigned NOT NULL,
            $table->integer('user_id', false, true); //' int(10) unsigned DEFAULT NULL,
            $table->integer('item_id', false, true); //' int(10) unsigned NOT NULL,
            $table->decimal('amount', 10, 2); //' decimal(10,2) NOT NULL DEFAULT '0.00',
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_dealer_refunds_items');
    }
}
