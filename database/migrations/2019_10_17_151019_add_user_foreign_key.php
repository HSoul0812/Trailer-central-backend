<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('auth_token')) {
            Schema::create('auth_token', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('access_token');
                $table->timestamps();
            });
        }

        Schema::table('auth_token', function (Blueprint $table) {
            if (Schema::hasColumn('auth_token', 'dealer_id')) {
                $table->dropColumn('dealer_id');
            }
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('dealer_id')->on('dealer');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('auth_token', function (Blueprint $table) {
            //
        });
    }
}
