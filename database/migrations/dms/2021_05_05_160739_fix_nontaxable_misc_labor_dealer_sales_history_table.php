<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixNontaxableMiscLaborDealerSalesHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $result = DB::select(DB::raw("
            SELECT i.id, sh.id dealer_sales_history_id, sum(ii.qty * ii.unit_price) non_taxable
            FROM `dealer_sales_history` sh
            JOIN qb_invoices i ON sh.tb_primary_id = i.id
            JOIN qb_invoice_items ii ON ii.invoice_id = i.id
            WHERE sh.`tb_name` = 'qb_invoices'
                AND sh.non_taxable = 0
                AND i.dealer_id = 6906
                AND i.unit_sale_id is null
                AND i.repair_order_id is null
                AND i.doc_num LIKE 'POS Sale%'
                AND ii.description LIKE 'Parts (Misc labor)%'
            GROUP BY i.id
        "));

        foreach ($result as $item) {
            DB::table('dealer_sales_history')
                ->where(['id' => $item->dealer_sales_history_id])
                ->update(['non_taxable' => $item->non_taxable]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
