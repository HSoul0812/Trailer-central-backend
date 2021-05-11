<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateServiceHrsReportView extends Migration
{

    private const SERVICE_HRS_REPORT_VIEW_NAME = 'dms_service_hrs_report';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() : void
    {
        DB::statement($this->dropView());

        DB::statement($this->createView());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() : void
    {
        DB::statement($this->dropView());
    }

    private function dropView() : string
    {
        return "
            DROP VIEW IF EXISTS `".self::SERVICE_HRS_REPORT_VIEW_NAME."`;
            ";
    }

    private function createView() : string
    {
        return "

          CREATE VIEW `trailercentral`.`".self::SERVICE_HRS_REPORT_VIEW_NAME."` AS

          SELECT CONCAT(MONTHNAME(dms_repair_order.created_at), ' ', YEAR(dms_repair_order.created_at), ' (', `dms_repair_order`.`dealer_id`, ')') as month_name,
          qb_items.type,
          SUM(qb_invoice_items.unit_price) as unit_price,
          DATE_FORMAT(dms_repair_order.created_at, '%Y-%m-%d') as `created_at`,
          `dms_repair_order`.`dealer_id`
          FROM `dms_repair_order`
          INNER JOIN qb_invoices ON qb_invoices.repair_order_id = dms_repair_order.id
          INNER JOIN qb_invoice_items ON qb_invoices.id = qb_invoice_items.invoice_id
          INNER JOIN qb_items ON qb_invoice_items.item_id = qb_items.id AND qb_items.type != 'tax' AND qb_items.type != 'discount'
          GROUP BY month_name, qb_items.type

        ";
    }
}
