<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UnitSaleAddMeta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_unit_sale', function (Blueprint $table) {
            //
            $table->text('meta')->nullable();
        });

        Schema::table('dms_quote_inventory', function (Blueprint $table) {
            //
            $table->text('meta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_unit_sale', function (Blueprint $table) {
            //
            $table->dropColumn('meta');
        });

        Schema::table('dms_quote_inventory', function (Blueprint $table) {
            //
            $table->dropColumn('meta');
        });
    }
}
