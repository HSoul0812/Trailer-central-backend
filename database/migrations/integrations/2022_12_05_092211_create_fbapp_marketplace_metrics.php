<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFbappMarketplaceMetrics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fbapp_marketplace_metrics', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('marketplace_id');
            $table->string('category', 64)->default('');
            $table->string('name', 64);
            $table->string('value', 64)->nullable();
            $table->timestamps();

            $table->foreign('marketplace_id')
                ->references('id')
                ->on('fbapp_marketplace');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fbapp_marketplace_metrics');
    }
}
