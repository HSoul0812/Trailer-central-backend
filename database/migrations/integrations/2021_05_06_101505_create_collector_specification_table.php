<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectorSpecificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collector_specification', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('collector_id')->nullable();
            $table->enum('logical_operator', ['and', 'or']);
            $table->timestamps();
        });

        Schema::table('collector_specification', function (Blueprint $table) {
            $table->foreign('collector_id')
                ->references('id')
                ->on('collector')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('collector_specification');
    }
}
