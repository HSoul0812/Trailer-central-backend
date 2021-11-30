<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJsonFieldsToCollectorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('collector', function (Blueprint $table) {
            $table->string('api_url')->nullable();
            $table->string('api_key_name')->nullable();
            $table->string('api_key_value')->nullable();
            $table->text('api_params')->nullable();
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
            $table->dropColumn('api_url');
            $table->dropColumn('api_key_name');
            $table->dropColumn('api_key_value');
            $table->dropColumn('api_params');
        });
    }
}
