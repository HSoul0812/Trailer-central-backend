<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxableColumnToQbInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qb_invoices', function (Blueprint $table) {
            $table->decimal('taxable_total', 8, 2)->after('total_tax')->default(0.00);
            $table->decimal('nontaxable_total', 8, 2)->after('taxable_total')->default(0.00);
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
            $table->dropColumn('taxable_total');
            $table->dropColumn('nontaxable_total');
        });
    }
}