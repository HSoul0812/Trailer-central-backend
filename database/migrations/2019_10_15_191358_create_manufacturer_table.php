<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManufacturerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('part_manufacturers')) {
            Schema::create('part_manufacturers', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 50)->unique();
                $table->timestamps();
            });
        }   
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('part_manufacturers');
    }
}
