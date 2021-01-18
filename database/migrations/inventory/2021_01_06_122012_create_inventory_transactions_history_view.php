<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateInventoryTransactionsHistoryView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::statement($this->dropView());

        $sql = <<<SQL
CREATE VIEW inventory_transaction_history AS
    SELECT RO.id,
           C.id AS customer_id,
           C.display_name AS customer_name,
           RO.inventory_id,
           I.vin,
           RO.created_at,
           RO.type COLLATE utf8_unicode_ci AS subtype,
           SI.problem COLLATE utf8_unicode_ci AS text_1,
           SI.cause COLLATE utf8_unicode_ci AS text_2,
           SI.solution COLLATE utf8_unicode_ci AS text_3,
           SI.amount AS sub_total,
           RO.total_price AS total,
           'repair-order' AS type
    FROM dms_repair_order RO
    LEFT JOIN dms_service_item SI ON SI.repair_order_id = RO.id
    JOIN inventory I ON RO.inventory_id = I.inventory_id
    LEFT JOIN dms_customer C ON RO.customer_id = C.id -- inventories from Repair Orders

    UNION

    SELECT US.id,
           US.buyer_id AS customer_id,
           C.display_name AS customer_name,
           US.inventory_id,
           COALESCE(I.vin COLLATE utf8_unicode_ci, US.inventory_vin) AS vin,
           US.created_at,
           NULL AS subtype,
           US.title AS text_1,
           US.admin_note AS text_2,
           NULL AS text_3,
           US.subtotal AS sub_total,
           US.total_price AS total,
           'quote' AS type
    FROM dms_unit_sale US
    LEFT JOIN inventory I ON US.inventory_id = I.inventory_id
    LEFT JOIN dms_customer C ON US.buyer_id = C.id -- inventories from Quotes

    UNION

    SELECT IV.id,
           IV.customer_id,
           I.item_primary_id AS inventory_id,
           C.display_name AS customer_name,
           IT.vin,
           IV.invoice_date AS created_at,
           IV.format AS subtype,
           IV.doc_num AS text_1,
           IV.memo AS text_2,
           SR.receipt_path AS text_3,
           (II.unit_price * II.qty) AS sub_total,
           IV.total AS total,
           'POS' AS type
    FROM qb_invoice_items II
    JOIN qb_items I ON II.item_id = I.id
    JOIN inventory IT ON IT.inventory_id = I.item_primary_id
    JOIN qb_invoices IV ON II.invoice_id = IV.id
    LEFT JOIN crm_pos_sales PO ON PO.invoice_id = IV.id
    LEFT JOIN dealer_sales_receipt SR ON SR.tb_primary_id = PO.id AND SR.tb_name = 'crm_pos_sales'
    LEFT JOIN dms_customer C ON IV.customer_id = C.id
    WHERE I.type = 'trailer' -- inventories from POS
SQL;
        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement($this->dropView());
    }

    private function dropView(): string
    {
        return <<<SQL
DROP VIEW IF EXISTS inventory_transaction_history;
SQL;
    }
}
