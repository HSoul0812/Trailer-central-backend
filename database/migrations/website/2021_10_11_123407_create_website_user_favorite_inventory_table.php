<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebsiteUserFavoriteInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('website_user_favorite_inventory', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('website_user_id');
            $table->unsignedInteger('inventory_id');
            $table->foreign('website_user_id')->references('id')->on('website_user')->onDelete('cascade');
            $table->foreign('inventory_id')->references('inventory_id')->on('inventory')->onDelete('cascade');
            $table->unique(['website_user_id', 'inventory_id']);
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
        Schema::dropIfExists('website_user_favorite_inventory');
    }
}
