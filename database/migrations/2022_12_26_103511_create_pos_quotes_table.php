<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosQuotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_quotes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('dealer_id');
            $table->text('quote_details');
            $table->timestamps();

            $table->index(['dealer_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pos_quotes');
    }
}
