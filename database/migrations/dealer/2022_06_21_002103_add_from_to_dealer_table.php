<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFromToDealerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealer', function (Blueprint $table) {
            $table->enum('from', ['trailertrader', 'trailercentral'])
                ->default('trailercentral');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealer', function (Blueprint $table) {
            $table->dropColumn('from');
        });
    }
}
