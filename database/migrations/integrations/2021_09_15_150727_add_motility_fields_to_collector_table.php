<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMotilityFieldsToCollectorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('collector', function (Blueprint $table) {
            $table->string('motility_username')->nullable();
            $table->string('motility_password')->nullable();
            $table->string('motility_account_no')->nullable();
            $table->string('motility_integration_id')->nullable();
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
          $table->dropColumn('motility_username');
          $table->dropColumn('motility_password');
          $table->dropColumn('motility_account_no');
          $table->dropColumn('motility_integration_id');
        });
    }
}
