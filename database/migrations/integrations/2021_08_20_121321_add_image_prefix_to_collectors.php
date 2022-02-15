<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddImagePrefixToCollectors extends Migration
{
  
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('collector', function (Blueprint $table) {
            $table->string('local_image_directory_address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('collector', function (Blueprint $table) {
            $table->dropColumn('local_image_directory_address');
        });
    }
}
