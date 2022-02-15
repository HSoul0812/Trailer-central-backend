<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFbappChatSalespeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fbapp_chat_salespeople', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('fbapp_chat_id');
            $table->foreign('fbapp_chat_id')->references('id')->on('fbapp_chat')->onDelete('cascade');
            $table->integer('sales_person_id')->unsigned();
            $table->foreign('sales_person_id')->references('id')->on('crm_sales_person')->onDelete('cascade');
            $table->unique(['fbapp_chat_id', 'sales_person_id']);
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
        Schema::dropIfExists('fbapp_chat_salespeople');
    }
}
