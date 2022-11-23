<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FbappMarketplaceMetrics extends Migration
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
            $table->unsignedInteger('fbapp_marketplace_id');
            $table->string('category', 64)->default('');
            $table->string('name', 64);
            $table->string('value', 512)->nullable();
            $table->timestamps();

            $table->foreign('fbapp_marketplace_id')
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
        Schema::drop('fbapp_marketplace_metrics');
    }
}
