<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFbappChatSalesPersonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fbapp_chat_sales_person', function (Blueprint $table) {
            $table->unsignedBigInteger('fbapp_chat_id');
            $table->foreign('fbapp_chat_id')->references('id')->on('fbapp_chat')->onDelete('cascade');
            $table->integer('sales_person_id')->unsigned();
            $table->foreign('sales_person_id')->references('id')->on('crm_sales_person')->onDelete('cascade');
            $table->primary(['fbapp_chat_id', 'sales_person_id']);
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
        Schema::dropIfExists('fbapp_chat_sales_person');
    }
}
