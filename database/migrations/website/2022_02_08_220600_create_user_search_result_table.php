<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSearchResultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('website_user_search_result', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('website_user_id');
            $table->text('search_url');
            $table->foreign('website_user_id')->references('id')->on('website_user')->onDelete('cascade');
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
        Schema::dropIfExists('website_user_search_result');
    }
}
