<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxBeforeTradeToInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qb_invoices', function (Blueprint $table) {
            $table->tinyInteger('tax_before_trade')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qb_invoices', function (Blueprint $table) {
            $table->dropColumn('tax_before_trade');
        });
    }
}
