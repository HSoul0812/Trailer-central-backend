<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSkipLocationsToCollectorTable extends Migration
{ 
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('collector', function (Blueprint $table) {
            $table->text('skip_locations')->nullable();
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
            $table->dropColumn('skip_locations');
        });
    }
}
