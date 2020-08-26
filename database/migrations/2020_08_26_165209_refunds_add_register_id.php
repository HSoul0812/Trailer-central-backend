<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefundsAddRegisterId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealer_refunds', function (Blueprint $table) {
            //
            $table->unsignedInteger('register_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealer_refunds', function (Blueprint $table) {
            //
            $table->dropColumn('register_id');
        });
    }
}
