<?php

use App\Models\Marketing\Facebook\Marketplace;
use App\Models\Marketing\Facebook\Listings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFbappListingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fbapp_listings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('marketplace_id');
            $table->integer('inventory_id');
            $table->bigInteger('facebook_id')->unique();
            $table->enum('account_type', Marketplace::ACCOUNT_TYPES);
            $table->integer('page_id')->default(0);
            $table->enum('listing_type', array_keys(Listings::LISTING_TYPES));
            $table->enum('specific_type', Listings::getAllSpecificTypes());
            $table->tinyInteger('year');
            $table->decimal('price');
            $table->string('make');
            $table->string('model');
            $table->string('description');
            $table->string('location');
            $table->string('color_exterior');
            $table->string('color_interior');
            $table->string('trim')->nullable();
            $table->integer('mileage')->nullable();
            $table->string('body_style')->nullable();
            $table->string('condition')->nullable();
            $table->string('transmission')->nullable();
            $table->string('fuel_type')->nullable();
            $table->enum('status', Listings::STATUSES);
            $table->timestamps();

            $table->index(['account_type', 'page_id']);
            $table->index(['marketplace_id', 'inventory_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fbapp_listings');
    }
}
