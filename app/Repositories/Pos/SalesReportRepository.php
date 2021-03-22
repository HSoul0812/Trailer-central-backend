<?php

declare(strict_types=1);

namespace App\Repositories\Pos;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SalesReportRepository implements SalesReportRepositoryInterface
{
    public const MIXED_REPORT_TYPE = 'mixed';
    public const PARTS_REPORT_TYPE = 'parts';
    public const UNITS_REPORT_TYPE = 'units';

    private $customReportHelpers = [
        'partBoundParams' => [],
        'inventoryBoundParams' => [],
        'partsWhere' => ['part_qb' => '', 'part_pos' => ''],
        'inventoryWhere' => ['inventory_qb' => '', 'inventory_pos' => '']
    ];

    /**
     * Builds the data for the custom sales report.
     *
     * Also it validates if there are the minimum required parameters
     *
     * @param array $params
     * @return array[]
     *
     * @throws InvalidArgumentException when the parameter "dealer_id" was not provided
     * @throws InvalidArgumentException when the date range parameters were not provided
     */
    public function customReport(array $params): array
    {
        /**
         * Given this report could have a big load, it is necessary to limit this query with dealer_id and date range
         */
        if (empty($params['dealer_id'])) {
            throw new InvalidArgumentException('Parameter "dealer_id" is required');
        }

        if (empty($params['from_date']) && empty($params['to_date'])) {
            throw new InvalidArgumentException('Parameters "from_date" and "to_date" are required');
        }

        if (empty($params['report_type'])) {
            $params['report_type'] = self::MIXED_REPORT_TYPE;
        }

        // to prevent mix queries when there are exclusive filters
        if ($params['report_type'] === self::PARTS_REPORT_TYPE) {
            $params['major_unit_category'] = '';
            $params['model'] = '';
            $params['year'] = '';
        } elseif ($params['report_type'] === self::UNITS_REPORT_TYPE) {
            $params['part_category'] = '';
        }

        $this->applyBasicsFilterForCustomReport($params);
        $this->applyFeesFiltersForCustomReport($params);

        $partsSQL = <<<SQL
                    SELECT
                           DATE_FORMAT(qi.invoice_date, '%Y') AS year,
                           DATE_FORMAT(qi.invoice_date, '%M') AS month,
                           DATE_FORMAT(qi.invoice_date, '%d') AS day,
                           DATE_FORMAT(qi.invoice_date, '%Y-%m-%d') AS date,
                           p.title,
                           p.sku AS reference,
                           IFNULL(iitem.cost,0) * ii.qty AS cost,
                           IFNULL(ii.unit_price,0) * ii.qty AS price,
                           FLOOR(ii.qty) AS qty,
                           @refund:=IFNULL((SELECT SUM(rf.amount) FROM dealer_refunds_items rf WHERE rf.refunded_item_id = ii.id AND rf.refunded_item_tbl = 'qb_invoice_items'), 0) AS refund,
                           ROUND((IFNULL(ii.unit_price,0) * ii.qty) - (IFNULL(iitem.cost,0) * ii.qty) - @refund, 2) AS profit,
                           IF(
                              qi.unit_sale_id IS NOT NULL,
                              CONCAT('/bill-of-sale/edit/', qi.unit_sale_id),
                              (
                               SELECT GROUP_CONCAT(r.receipt_path)
                               FROM dealer_sales_receipt r
                               JOIN qb_payment p ON r.tb_primary_id = p.id
                               WHERE p.invoice_id = qi.id AND r.tb_name = 'qb_payment'
                               )
                           ) AS links,
                           ro.type AS ro_type,
                           'part-qb' AS source,
                           iitem.id AS item_id,
                           qi.id AS doc_id,
                           qi.doc_num AS doc_num,
                           CASE
                               WHEN qi.unit_sale_id IS NOT NULL THEN 'Deal'
                               WHEN qi.sales_person_id IS NOT NULL THEN 'POS'
                               ELSE 'Service'
                           END AS type
                    FROM dealer_sales_history sh
                    JOIN qb_invoices qi on sh.tb_primary_id = qi.id AND sh.tb_name = 'qb_invoices'
                    LEFT JOIN dms_repair_order ro on qi.repair_order_id = ro.id
                    JOIN qb_invoice_items ii on qi.id = ii.invoice_id
                    JOIN qb_items iitem on ii.item_id = iitem.id AND iitem.type = 'part'
                    JOIN parts_v1 p on iitem.item_primary_id = p.id
                    WHERE sh.dealer_location_id IN (SELECT l.dealer_location_id FROM dealer_location l WHERE l.dealer_id = :dealer_id_part_qb)
                          AND qi.invoice_date >= :from_date_part_qb AND qi.invoice_date <= :to_date_part_qb
                          {$this->customReportHelpers['partsWhere']['part_qb']}
                    UNION
                    SELECT
                        DATE_FORMAT(ps.created_at, '%Y') AS year,
                        DATE_FORMAT(ps.created_at, '%M') AS month,
                        DATE_FORMAT(ps.created_at, '%d') AS day,
                        DATE_FORMAT(ps.created_at, '%Y-%m-%d') AS date,
                        p.title,
                        p.sku AS reference,
                        IFNULL(iitem.cost,0)  * psp.qty AS cost,
                        IFNULL(psp.price,0) * psp.qty AS price,
                        FLOOR(psp.qty),
                        @refund:=IFNULL((SELECT SUM(rf.amount) FROM dealer_refunds_items rf WHERE rf.refunded_item_id = psp.id AND rf.refunded_item_tbl = 'crm_pos_sale_products'), 0) AS refund,
                        ROUND((IFNULL(psp.price,0) * psp.qty) - (IFNULL(iitem.cost,0) * psp.qty) - @refund, 2) AS profit,
                        (
                            SELECT GROUP_CONCAT(r.receipt_path)
                            FROM dealer_sales_receipt r
                            JOIN crm_pos_sales p ON r.tb_primary_id = p.id
                            WHERE r.tb_name = 'crm_pos_sales'
                        ) AS links,
                        '' AS ro_type,
                        'part-pos' AS source,
                        iitem.id AS item_id,
                        ps.id AS doc_id,
                        CONCAT('POS Sales # ',  ps.id) AS doc_num,
                        'POS' AS type
                    FROM dealer_sales_history sh
                    JOIN crm_pos_sales ps on sh.tb_primary_id = ps.id AND sh.tb_name = 'crm_pos_sales'
                    JOIN crm_pos_sale_products psp on ps.id = psp.sale_id
                    JOIN qb_items iitem on psp.item_id = iitem.id AND iitem.type = 'part'
                    JOIN parts_v1 p on iitem.item_primary_id = p.id
                    WHERE sh.dealer_location_id IN (SELECT l.dealer_location_id FROM dealer_location l WHERE l.dealer_id = :dealer_id_part_pos)
                          AND ps.created_at >= :from_date_part_pos AND ps.created_at <= :to_date_part_pos
                          {$this->customReportHelpers['partsWhere']['part_pos']}
SQL;

        $inventorySQL = <<<SQL
                SELECT
                    DATE_FORMAT(qi.invoice_date, '%Y') AS year,
                    DATE_FORMAT(qi.invoice_date, '%M') AS month,
                    DATE_FORMAT(qi.invoice_date, '%d') AS day,
                    DATE_FORMAT(qi.invoice_date, '%Y-%m-%d') AS date,
                    i.title,
                    i.stock AS reference,
                    IFNULL(iitem.cost,0) * ii.qty AS cost,
                    IFNULL(ii.unit_price,0) * ii.qty AS price,
                    FLOOR(ii.qty) AS qty,
                    @refund:=IFNULL((SELECT SUM(rf.amount) FROM dealer_refunds_items rf WHERE rf.refunded_item_id = ii.id AND rf.refunded_item_tbl = 'qb_invoice_items'), 0) AS refund,
                    ROUND((IFNULL(ii.unit_price,0) * ii.qty) - (IFNULL(iitem.cost,0) * ii.qty) - @refund, 2) AS profit,
                    IF(
                      qi.unit_sale_id IS NOT NULL,
                      CONCAT('/bill-of-sale/edit/', qi.unit_sale_id),
                      (
                       SELECT GROUP_CONCAT(r.receipt_path)
                       FROM dealer_sales_receipt r
                       JOIN qb_payment p ON r.tb_primary_id = p.id
                       WHERE p.invoice_id = qi.id AND r.tb_name = 'qb_payment'
                       )
                    ) AS links,
                    ro.type AS ro_type,
                    'inventory-qb' AS source,
                    iitem.id AS item_id,
                    qi.id AS doc_id,
                    qi.doc_num AS doc_num,
                    CASE
                               WHEN qi.unit_sale_id IS NOT NULL THEN 'Deal'
                               WHEN qi.sales_person_id IS NOT NULL THEN 'POS'
                               ELSE 'Service'
                    END AS type
                FROM dealer_sales_history sh
                JOIN qb_invoices qi on sh.tb_primary_id = qi.id AND sh.tb_name = 'qb_invoices'
                LEFT JOIN dms_repair_order ro on qi.repair_order_id = ro.id
                JOIN qb_invoice_items ii on qi.id = ii.invoice_id
                JOIN qb_items iitem on ii.item_id = iitem.id AND iitem.type = 'trailer'
                JOIN inventory i on iitem.item_primary_id = i.inventory_id
                WHERE
                      sh.dealer_location_id IN (SELECT l.dealer_location_id FROM dealer_location l WHERE l.dealer_id = :dealer_id_inventory_qb)
                      AND qi.invoice_date >= :from_date_inventory_qb AND qi.invoice_date <= :to_date_inventory_qb
                      {$this->customReportHelpers['inventoryWhere']['inventory_qb']}
                UNION
                SELECT
                        DATE_FORMAT(ps.created_at, '%Y') AS year,
                        DATE_FORMAT(ps.created_at, '%M') AS month,
                        DATE_FORMAT(ps.created_at, '%d') AS day,
                        DATE_FORMAT(ps.created_at, '%Y-%m-%d') AS date,
                        i.title,
                        i.stock AS reference,
                        IFNULL(iitem.cost,0) * psp.qty AS cost,
                        IFNULL(psp.price,0) * psp.qty AS price,
                        FLOOR(psp.qty),
                        @refund:=IFNULL((SELECT SUM(rf.amount) FROM dealer_refunds_items rf WHERE rf.refunded_item_id = psp.id AND rf.refunded_item_tbl = 'crm_pos_sale_products'), 0) AS refund,
                        ROUND((IFNULL(psp.price,0) * psp.qty) - (IFNULL(iitem.cost,0) * psp.qty) - @refund, 2) AS profit,
                        (
                            SELECT GROUP_CONCAT(r.receipt_path)
                            FROM dealer_sales_receipt r
                            JOIN crm_pos_sales p ON r.tb_primary_id = p.id
                            WHERE r.tb_name = 'crm_pos_sales'
                        ) AS links,
                        '' AS ro_type,
                        'inventory-pos' AS source,
                        iitem.id AS item_id,
                        ps.id AS doc_id,
                        CONCAT('POS Sales # ',  ps.id) AS doc_num,
                        'POS' AS type
                    FROM dealer_sales_history sh
                    JOIN crm_pos_sales ps on sh.tb_primary_id = ps.id AND sh.tb_name = 'crm_pos_sales'
                    JOIN crm_pos_sale_products psp on ps.id = psp.sale_id
                    JOIN qb_items iitem on psp.item_id = iitem.id AND iitem.type = 'trailer'
                    JOIN inventory i on iitem.item_primary_id = i.inventory_id
                    WHERE sh.dealer_location_id IN (SELECT l.dealer_location_id FROM dealer_location l WHERE l.dealer_id = :dealer_id_inventory_pos)
                          AND ps.created_at >= :from_date_inventory_pos AND ps.created_at <= :to_date_inventory_pos
                          {$this->customReportHelpers['inventoryWhere']['inventory_pos']}
SQL;

        if ($params['report_type'] === self::MIXED_REPORT_TYPE) {
            $boundParams = array_merge(
                $this->customReportHelpers['partBoundParams'],
                $this->customReportHelpers['inventoryBoundParams'],
                [
                    'dealer_id_part_qb' => $params['dealer_id'],
                    'dealer_id_part_pos' => $params['dealer_id'],
                    'dealer_id_inventory_qb' => $params['dealer_id'],
                    'dealer_id_inventory_pos' => $params['dealer_id'],
                    'from_date_part_qb' => $params['from_date'],
                    'from_date_part_pos' => $params['from_date'],
                    'from_date_inventory_qb' => $params['from_date'],
                    'from_date_inventory_pos' => $params['from_date'],
                    'to_date_part_qb' => $params['to_date'] . ' 23:59:59',
                    'to_date_part_pos' => $params['to_date'] . ' 23:59:59',
                    'to_date_inventory_qb' => $params['to_date'] . ' 23:59:59',
                    'to_date_inventory_pos' => $params['to_date'] . ' 23:59:59',
                ]);

            $sql = "$partsSQL \nUNION\n $inventorySQL";
        } elseif ($params['report_type'] === self::PARTS_REPORT_TYPE) {
            $boundParams = array_merge(
                $this->customReportHelpers['partBoundParams'],
                [
                    'dealer_id_part_qb' => $params['dealer_id'],
                    'dealer_id_part_pos' => $params['dealer_id'],
                    'from_date_part_qb' => $params['from_date'],
                    'from_date_part_pos' => $params['from_date'],
                    'to_date_part_qb' => $params['to_date'] . ' 23:59:59',
                    'to_date_part_pos' => $params['to_date'] . ' 23:59:59',
                ]);

            $sql = $partsSQL;
        } else {
            $boundParams = array_merge(
                $this->customReportHelpers['inventoryBoundParams'],
                [
                    'dealer_id_inventory_qb' => $params['dealer_id'],
                    'dealer_id_inventory_pos' => $params['dealer_id'],
                    'from_date_inventory_qb' => $params['from_date'],
                    'from_date_inventory_pos' => $params['from_date'],
                    'to_date_inventory_qb' => $params['to_date'] . ' 23:59:59',
                    'to_date_inventory_pos' => $params['to_date'] . ' 23:59:59',
                ]);

            $sql = $inventorySQL;
        }

        return DB::select(DB::raw($sql), $boundParams);
    }

    /**
     * Apply basics filters by query, part_category, major_unit_category, year, model
     *
     * @param array $params
     */
    private function applyBasicsFilterForCustomReport(array $params): void
    {
        if (!empty($params['query'])) {
            $query = '%' . $params['query'] . '%';

            foreach (['part_qb', 'part_pos'] as $suffix) {
                $this->customReportHelpers['partBoundParams']["part_description_{$suffix}"] = $query;
                $this->customReportHelpers['partBoundParams']["part_title_{$suffix}"] = $query;
                $this->customReportHelpers['partBoundParams']["part_sku_{$suffix}"] = $query;

                $this->customReportHelpers['partsWhere'][$suffix] .= " AND (
                            p.description LIKE :part_description_{$suffix} OR
                            p.title LIKE :part_title_{$suffix} OR
                            p.sku LIKE :part_sku_{$suffix}
                        )";
            }

            foreach (['inventory_qb', 'inventory_pos'] as $suffix) {
                $this->customReportHelpers['inventoryBoundParams']["unit_description_{$suffix}"] = $query;
                $this->customReportHelpers['inventoryBoundParams']["unit_title_{$suffix}"] = $query;
                $this->customReportHelpers['inventoryBoundParams']["unit_stock_{$suffix}"] = $query;

                $this->customReportHelpers['inventoryWhere'][$suffix] .= " AND (
                            i.description LIKE :unit_description_{$suffix} OR
                            i.title LIKE :unit_title_{$suffix} OR
                            i.stock LIKE :unit_stock_{$suffix}
                            )";
            }
        }

        if (!empty($params['part_category'])) {
            foreach (['part_qb', 'part_pos'] as $suffix) {
                $this->customReportHelpers['partBoundParams']["part_category_{$suffix}"] = $params['part_category'];
                $this->customReportHelpers['partsWhere'][$suffix] .= " AND p.category_id = :part_category_{$suffix}";
            }
        }

        if (!empty($params['major_unit_category'])) {
            foreach (['inventory_qb', 'inventory_pos'] as $suffix) {
                $this->customReportHelpers['inventoryBoundParams']["major_unit_category_{$suffix}"] = $params['major_unit_category'];
                $this->customReportHelpers['inventoryWhere'][$suffix] .= " AND i.category = :major_unit_category_{$suffix}";
            }
        }

        if (!empty($params['year'])) {
            foreach (['inventory_qb', 'inventory_pos'] as $suffix) {
                $this->customReportHelpers['inventoryBoundParams']["year_{$suffix}"] = $params['year'];
                $this->customReportHelpers['inventoryWhere'][$suffix] .= " AND i.year = :year_{$suffix}";
            }
        }

        if (!empty($params['model'])) {
            foreach (['inventory_qb', 'inventory_pos'] as $suffix) {
                $this->customReportHelpers['inventoryBoundParams']["model_{$suffix}"] = $params['model'];
                $this->customReportHelpers['inventoryWhere'][$suffix] .= " AND i.model LIKE :model_{$suffix}";
            }
        }
    }

    /**
     * @param array $params
     */
    private function applyFeesFiltersForCustomReport(array $params): void
    {
        if (!empty($params['fee_type']) && is_array($params['fee_type'])) {
            /**
             * There is not optimal way to relate invoices to fee types, so let's see the throughput
             */

            $feeFilter = ['part_qb' => [], 'part_pos' => []];

            foreach (['part_qb', 'part_pos'] as $suffix) {

                foreach ($params['fee_type'] as $fee) {
                    $text = '%' . strtoupper(str_replace('_', ' ', $fee)) . '%'; // snake to human text

                    $this->customReportHelpers['partBoundParams']["{$fee}_{$suffix}"] = $text;

                    $feeFilter[$suffix][] = "sqi.name LIKE :{$fee}_{$suffix}";
                }
            }

            $this->customReportHelpers['partsWhere']['part_qb'] .= ' AND EXISTS (SELECT r.id FROM qb_invoice_items r
                            JOIN qb_items sqi ON r.item_id = sqi.id
                            WHERE r.invoice_id = qi.id AND (' . implode(' OR ', $feeFilter['part_qb']) . '))';
            $this->customReportHelpers['partsWhere']['part_pos'] .= ' AND EXISTS (SELECT r.id FROM crm_pos_sale_products r
                            JOIN qb_items sqi ON r.item_id = sqi.id
                            WHERE r.sale_id = ps.id AND (' . implode(' OR ', $feeFilter['part_pos']) . '))';

            $feeFilter = ['inventory_qb' => [], 'inventory_pos' => []];

            foreach (['inventory_qb', 'inventory_pos'] as $suffix) {

                foreach ($params['fee_type'] as $fee) {
                    $text = '%' . strtoupper(str_replace('_', ' ', $fee)) . '%'; // snake to human text

                    $this->customReportHelpers['inventoryBoundParams']["{$fee}_{$suffix}"] = $text;

                    $feeFilter[$suffix][] = "sqi.name LIKE :{$fee}_{$suffix}";
                }
            }

            $this->customReportHelpers['inventoryWhere']['inventory_qb'] .= ' AND EXISTS (SELECT r.id FROM qb_invoice_items r
                            JOIN qb_items sqi ON r.item_id = sqi.id
                            WHERE r.invoice_id = qi.id AND (' . implode(' OR ', $feeFilter['inventory_qb']) . '))';
            $this->customReportHelpers['inventoryWhere']['inventory_pos'] .= ' AND EXISTS (SELECT r.id FROM crm_pos_sale_products r
                            JOIN qb_items sqi ON r.item_id = sqi.id
                            WHERE r.sale_id = ps.id AND (' . implode(' OR ', $feeFilter['inventory_pos']) . '))';
        }
    }
}
