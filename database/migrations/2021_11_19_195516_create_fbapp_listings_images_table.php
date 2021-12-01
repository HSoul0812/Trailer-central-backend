<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFbappListingsImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fbapp_listings_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('listing_id')->index();
            $table->integer('image_id');
            $table->timestamps();

            $table->unique('listing_id', 'image_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fbapp_listings_images');
    }
}
