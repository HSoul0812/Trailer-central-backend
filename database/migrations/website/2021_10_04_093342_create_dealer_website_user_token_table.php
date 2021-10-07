<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealerWebsiteUserTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealer_website_user_token', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('access_token', 255);
            $table->bigInteger('dealer_website_user_id')->unsigned();
            $table->foreign('dealer_website_user_id')->references('id')->on('dealer_website_user')->onDelete('cascade');
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
        Schema::dropIfExists('dealer_website_user_token');
    }
}
