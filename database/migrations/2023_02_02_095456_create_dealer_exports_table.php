<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealerExportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealer_exports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('dealer_id')->references('id')->on('user');
            $table->string('entity_type');
            $table->string('file_path')->nullable();
            $table->unsignedTinyInteger('status')->default(0)->comment('0 => queued, 1 => processing, 2 => processed');
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
        Schema::dropIfExists('dealer_exports');
    }
}
