<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Dms413PartsAddMinMax extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parts_v1', function (Blueprint $table) {
            $table->integer('stock_min')->nullable();
            $table->integer('stock_max')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parts_v1', function (Blueprint $table) {
            $table->dropColumn('stock_min');
            $table->dropColumn('stock_max');
        });
    }
}
