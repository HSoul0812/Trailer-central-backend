<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qb_payment', function (Blueprint $table) {
            $table->index('invoice_id', 'INVOICE_LOOKUP');
        });

        Schema::table('qb_invoices', function (Blueprint $table) {
            $table->index('unit_sale_id', 'QUOTE_LOOKUP');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qb_payment', function (Blueprint $table) {
            $table->dropIndex('INVOICE_LOOKUP');
        });

        Schema::table('qb_invoices', function (Blueprint $table) {
            $table->dropIndex('QUOTE_LOOKUP');
        });
    }
}
