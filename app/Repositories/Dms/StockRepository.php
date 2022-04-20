<?php

declare(strict_types=1);

namespace App\Repositories\Dms;

use Generator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Handles all report queries related with inventories (major units) and parts (they're commonly called stocks)
 */
class StockRepository implements StockRepositoryInterface
{
    private $financialReportHelpers = [
        'partBoundParams' => [],
        'inventoryBoundParams' => [],
        'searchWhereForParts' => '',
        'searchWhereForInventories' => '',
        'dateRangeWhereForParts' => '',
        'dateRangeWhereForInventories' => '',
        'type_of_stock' => self::STOCK_TYPE_MIXED
    ];

    /**
     * Handles the financial reports
     *
     * @param array $params
     * @return array<array>
     * @throws InvalidArgumentException when the dealer_id param was not provided
     */
    public function financialReport(array $params): array
    {
        $this->applyFiltersForFinancialReport($params);

        $sqlParts = <<<SQL
            SELECT IFNULL((SELECT l.balance
                           FROM parts_audit_log l
                           WHERE l.part_id = p.id AND l.bin_id = bq.bin_id
                                 {$this->financialReportHelpers['dateRangeWhereForParts']}
                           ORDER BY l.created_at DESC
                           LIMIT 1), bq.qty) AS qty,
               pb.bin_name             AS bin_name,
               bq.bin_id               AS bin_id,
               p.id                    AS id,
               p.title                 AS title,
               p.sku                   AS reference,
               p.price                 AS price,
               p.dealer_cost           AS dealer_cost,
               p.price - p.dealer_cost AS profit,
               'parts'                 AS source
        FROM dms_settings_part_bin pb
                 LEFT JOIN part_bin_qty bq ON pb.id = bq.bin_id AND bq.qty > 0
                 LEFT JOIN parts_v1 p ON bq.part_id = p.id
        WHERE p.dealer_id = :dealer_id_parts AND p.id IS NOT NULL
              {$this->financialReportHelpers['searchWhereForParts']}
SQL;

        $sqlInventories = <<<SQL
            SELECT 1                                                AS qty,
                   'na'                                             AS bin_name,
                   -1                                               AS bin_id,
                   i.inventory_id                                   AS id,
                   i.title                                          AS title,
                   i.stock                                          AS reference,
                   i.price                                          AS price,
                   CAST(i.cost_of_unit AS DECIMAL(10, 2))           AS dealer_cost,
                   i.price - CAST(i.cost_of_unit AS DECIMAL(10, 2)) AS profit,
                   'inventories'                                    AS source
            FROM inventory i
            LEFT JOIN dms_customer_inventory ci ON ci.inventory_id = i.inventory_id
            WHERE 1=1
                  AND i.dealer_id = :dealer_id_inventories
                  AND i.inventory_id IS NOT NULL
                  AND ci.inventory_id IS NULL
                  AND i.is_archived = 0
                  AND i.status != 2
                  {$this->financialReportHelpers['searchWhereForInventories']}
                  AND NOT EXISTS (
                    SELECT d.invoice_date FROM dms_quote_inventory qi
                        JOIN dms_unit_sale us on us.id = qi.quote_id
                        JOIN qb_invoices d on (us.id = d.unit_sale_id AND d.doc_num NOT LIKE 'DP-%')
                        WHERE qi.inventory_id = i.inventory_id {$this->financialReportHelpers['dateRangeWhereForInventories']}_1
                    UNION
                    SELECT d.invoice_date FROM dms_unit_sale us
                        JOIN qb_invoices d on (us.id = d.unit_sale_id AND d.doc_num NOT LIKE 'DP-%')
                        WHERE us.inventory_id = i.inventory_id {$this->financialReportHelpers['dateRangeWhereForInventories']}_2
                )
SQL;

        $sql = "$sqlParts \nUNION\n $sqlInventories";

        $dbParams = array_merge(
            $this->financialReportHelpers['partBoundParams'],
            $this->financialReportHelpers['inventoryBoundParams']
        );

        if ($this->financialReportIsOnlyAboutParts()) {
            $sql = $sqlParts;
            $dbParams = $this->financialReportHelpers['partBoundParams'];
        } else if ($this->financialReportIsOnlyAboutInventories()) {
            $sql = $sqlInventories;
            $dbParams = $this->financialReportHelpers['inventoryBoundParams'];
        }

        /** @var Generator $cursor */
        $rows = DB::cursor(DB::raw($sql), $dbParams);
        $report = [];

        foreach ($rows as $row) {
            $report[$row->id . '-' . $row->source][$row->bin_id]['part'] = $row;
        }

        return $report;
    }

    /**
     * @param array $params
     * @throws InvalidArgumentException when the dealer_id param was not provided
     */
    private function applyFiltersForFinancialReport(array $params): void
    {
        $this->financialReportHelpers['type_of_stock'] = $params['type_of_stock'] ?? self::STOCK_TYPE_MIXED;

        if (empty($params['dealer_id'])) {
            // since there are many records, it is necessary to filter by dealer
            throw new InvalidArgumentException("The 'dealer_id' argument is required");
        }

        $this->financialReportHelpers['partBoundParams'] = ['dealer_id_parts' => $params['dealer_id']];
        $this->financialReportHelpers['inventoryBoundParams'] = ['dealer_id_inventories' => $params['dealer_id']];

        if (empty($params['to_date'])) {
            $params['to_date'] = date('Y-m-d'); // default end of date range
        }

        $this->financialReportHelpers['partBoundParams']['to_date_parts'] = $params['to_date'] . ' 12:00:00';
        $this->financialReportHelpers['inventoryBoundParams']+=[
            'to_date_inventories_1' => $params['to_date'] . ' 12:00:00',
            'to_date_inventories_2' => $params['to_date'] . ' 12:00:00',
        ];

        $this->financialReportHelpers['dateRangeWhereForParts'] = " AND l.created_at <= :to_date_parts";
        $this->financialReportHelpers['dateRangeWhereForInventories'] = " AND d.invoice_date <= :to_date_inventories";

        if (!empty($params['search_term'])) {
            $searchTerm = "%{$params['search_term']}%";

            $this->financialReportHelpers['partBoundParams']['title_parts'] = $searchTerm;
            $this->financialReportHelpers['partBoundParams']['sku'] = $searchTerm;
            $this->financialReportHelpers['partBoundParams']['bin'] = $searchTerm;

            $this->financialReportHelpers['inventoryBoundParams']['title_inventories'] = $searchTerm;
            $this->financialReportHelpers['inventoryBoundParams']['stock'] = $searchTerm;

            $this->financialReportHelpers['searchWhereForParts'] = " AND (p.title LIKE :title_parts OR p.sku LIKE :sku OR pb.bin_name LIKE :bin)";
            $this->financialReportHelpers['searchWhereForInventories'] = " AND (i.title LIKE :title_inventories OR i.stock LIKE :stock)";
        }
    }

    private function financialReportIsOnlyAboutParts(): bool
    {
        return $this->financialReportHelpers['type_of_stock'] === self::STOCK_TYPE_PARTS;
    }

    private function financialReportIsOnlyAboutInventories(): bool
    {
        return $this->financialReportHelpers['type_of_stock'] === self::STOCK_TYPE_INVENTORIES;
    }
}
