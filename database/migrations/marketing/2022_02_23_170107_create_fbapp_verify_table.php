<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFbappVerifyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fbapp_verify', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('twilio_number', 12)->index();
            $table->string('response');
            $table->string('code');
            $table->boolean('success')->nullable();
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
        Schema::dropIfExists('fbapp_verify');
    }
}
