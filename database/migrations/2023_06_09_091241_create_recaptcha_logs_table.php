<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecaptchaLogsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('recaptcha_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedDouble('score');
            $table->string('user_agent');
            $table->string('ip');
            $table->string('action');
            $table->string('path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('recaptcha_logs');
    }
}
