<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutoCreatedToVendor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qb_vendors', function (Blueprint $table) {
            $table->tinyInteger('auto_created')->default(0)->after('show_on_floorplan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qb_vendors', function (Blueprint $table) {
            $table->dropColumn('auto_created');
        });
    }
}
