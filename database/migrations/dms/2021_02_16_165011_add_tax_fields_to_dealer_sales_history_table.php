<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTaxFieldsToDealerSalesHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealer_sales_history', function (Blueprint $table) {
            $table->decimal('tax_rate', 4, 4)->after('is_local_tax')->default('0.00');
            $table->decimal('non_taxable', 10, 2)->after('total_tax')->default('0.00');
        });

        DB::table('dealer_sales_history')
            ->select('tb_name', 'tb_primary_id')
            ->orderBy('id')
            ->where(['tb_name' => 'qb_invoices'])
            ->chunk(500, function ($dealerSales) {
                foreach ($dealerSales as $dealerSale) {
                    $invoice = DB::table('qb_invoices')->select('tax_rate')->find($dealerSale->tb_primary_id);

                    if (!$invoice || $invoice->tax_rate <= 0) {
                        continue;
                    }

                    DB::table('dealer_sales_history')
                        ->where([
                            ['tb_name', '=', 'qb_invoices'],
                            ['tb_primary_id', '=', $dealerSale->tb_primary_id]
                        ])
                        ->update(['tax_rate' => $invoice->tax_rate]);
                }
        });

        DB::table('dealer_sales_history')
            ->select('tb_primary_id', 'total')
            ->orderBy('id')
            ->where([
                ['state_tax', '=', 0],
                ['county_tax', '=', 0],
                ['city_tax', '=', 0],
                ['district1_tax', '=', 0],
                ['district2_tax', '=', 0],
                ['district3_tax', '=', 0],
                ['district4_tax', '=', 0],
            ])
            ->chunk(500, function ($dealerSales) {
                foreach ($dealerSales as $dealerSale) {
                    DB::table('dealer_sales_history')
                        ->where(['tb_primary_id' => $dealerSale->tb_primary_id])
                        ->update(['non_taxable' => $dealerSale->total]);
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealer_sales_history', function (Blueprint $table) {
            $table->dropColumn('tax_rate');
            $table->dropColumn('non_taxable');
        });
    }
}
