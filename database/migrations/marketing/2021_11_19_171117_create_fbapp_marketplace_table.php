<?php

use App\Models\Marketing\Facebook\Marketplace;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFbappMarketplaceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fbapp_marketplace', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dealer_id')->index();
            $table->integer('dealer_location_id')->index();
            $table->string('page_url');
            $table->string('fb_username');
            $table->string('fb_password');
            $table->string('tfa_username');
            $table->string('tfa_password');
            $table->enum('tfa_type', array_keys(Marketplace::TFA_TYPES));
            $table->timestamps();

            $table->index(['dealer_id', 'dealer_location_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fbapp_marketplace');
    }
}
