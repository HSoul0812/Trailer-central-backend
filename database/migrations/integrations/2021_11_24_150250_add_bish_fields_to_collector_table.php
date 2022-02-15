<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBishFieldsToCollectorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('collector', function (Blueprint $table) {
            $table->integer('api_max_records')->nullable();
            $table->integer('api_pagination')->nullable();
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
            $table->dropColumn('api_max_records');
            $table->dropColumn('api_pagination');
        });
    }
}
