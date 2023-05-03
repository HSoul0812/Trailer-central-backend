<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViewedDealersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('viewed_dealers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('dealer_id');
            $table->unsignedBigInteger('inventory_id');
            $table->timestamps();

            // We maintain uniqueness in TT even though it's not unique in TC
            // we need to do this because we have a feature to get dealer id from name
            $table->unique(['dealer_id']);
            $table->unique(['name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('viewed_dealers');
    }
}
