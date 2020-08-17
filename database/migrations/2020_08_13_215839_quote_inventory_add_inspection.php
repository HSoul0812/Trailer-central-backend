<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class QuoteInventoryAddInspection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_quote_inventory', function (Blueprint $table) {
            $table->string('inspection_cert')->nullable();
            $table->date('inspection_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_quote_inventory', function (Blueprint $table) {
            $table->dropColumn('inspection_cert');
            $table->dropColumn('inspection_date');
        });
    }
}
