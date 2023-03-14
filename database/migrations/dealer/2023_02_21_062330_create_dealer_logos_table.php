<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealerLogosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealer_logos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('dealer_id');
            $table->string('filename');
            $table->string('benefit_statement')->nullable();
            $table->foreign('dealer_id')->references('dealer_id')->on('dealer')->onDelete('cascade');
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
        Schema::dropIfExists('dealer_logos');
    }
}
