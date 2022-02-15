<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateTaxRateToDealerSalesHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('dealer_sales_history')
            ->select(
                'id',
                'total',
                'state_tax',
                'county_tax',
                'city_tax',
                'district1_tax',
                'district2_tax',
                'district3_tax',
                'district4_tax'
            )
            ->orderBy('id')
            ->where('tax_rate', '=', 0)
            ->where('total', '>', 0)
            ->where(function ($query) {
                $query->where('state_tax', '>', 0)
                    ->orWhere('county_tax', '>', 0)
                    ->orWhere('city_tax', '>', 0)
                    ->orWhere('district1_tax', '>', 0)
                    ->orWhere('district2_tax', '>', 0)
                    ->orWhere('district3_tax', '>', 0)
                    ->orWhere('district4_tax', '>', 0);
            })
            ->chunk(500, function ($dealerSales) {
                foreach ($dealerSales as $dealerSale) {
                    $taxRate = (
                        $dealerSale->state_tax +
                        $dealerSale->county_tax +
                        $dealerSale->city_tax +
                        $dealerSale->district1_tax +
                        $dealerSale->district2_tax +
                        $dealerSale->district3_tax +
                        $dealerSale->district4_tax
                        ) / $dealerSale->total;

                    DB::table('dealer_sales_history')
                        ->where(['id' => $dealerSale->id])
                        ->update(['tax_rate' => round($taxRate, 4)]);
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

    }
}
