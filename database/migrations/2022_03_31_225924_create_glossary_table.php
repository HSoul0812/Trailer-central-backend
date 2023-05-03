<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlossaryTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('glossary', function (Blueprint $table) {
            $table->id();
            $table->string('denomination');
            $table->text('short_description')->nullable();
            $table->text('long_description')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('glossary');
    }
}
