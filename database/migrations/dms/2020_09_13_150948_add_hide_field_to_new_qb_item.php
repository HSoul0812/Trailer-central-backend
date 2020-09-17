<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHideFieldToNewQbItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qb_items_new', function (Blueprint $table) {
            $table->tinyInteger('hide')->default(0)->after('in_simple_mode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qb_items_new', function (Blueprint $table) {
            $table->dropColumn('hide');
        });
    }
}
