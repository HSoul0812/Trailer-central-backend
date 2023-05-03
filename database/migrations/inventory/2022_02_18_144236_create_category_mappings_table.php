<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryMappingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('category_mappings', function (Blueprint $table) {
            $table->id()->unsigned();
            $table->integer('category_id')->unsigned()->index('category_mappings_i_category_id');
            $table->string('map_from');
            $table->string('map_to');
            $table->string('type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('category_mappings');
    }
}
