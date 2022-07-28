<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableWebsiteFavoritesExports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('website_favorites_exports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('website_id')->unsigned();
            $table->timestamp('last_ran');
            $table->foreign('website_id')->references('id')->on('website')->onDelete('cascade');
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
        Schema::dropIfExists('website_favorites_exports');
    }
}
