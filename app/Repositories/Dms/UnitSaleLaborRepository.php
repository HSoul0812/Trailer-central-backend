<?php

namespace App\Repositories\Dms;

use App\Models\CRM\Dms\UnitSale;
use App\Models\CRM\Dms\UnitSaleLabor;
use App\Repositories\RepositoryAbstract;
use Illuminate\Support\Facades\DB;

/**
 * Class UnitSaleLaborRepository
 * @package App\Repositories\Dms
 */
class UnitSaleLaborRepository extends RepositoryAbstract implements UnitSaleLaborRepositoryInterface
{
    /**
     * @param $params
     * @return array
     */
    public function getTechnicians($params): array
    {
        $unitSaleTable = UnitSale::getTableName();
        $unitSaleLaborTable = UnitSaleLabor::getTableName();

        return DB::table($unitSaleLaborTable)
            ->join($unitSaleTable, "{$unitSaleTable}.id", '=', "{$unitSaleLaborTable}.unit_sale_id")
            ->select("{$unitSaleLaborTable}.technician")
            ->groupBy("{$unitSaleLaborTable}.technician")
            ->where("{$unitSaleTable}.dealer_id", '=', $params['dealer_id'])
            ->get()
            ->pluck('technician')
            ->toArray();
    }

    /**
     * @param $params
     * @return array
     */
    public function serviceReport($params): array
    {
        $dbParams = ['dealerId' => $params['dealer_id']];
        $usWhere = "";
        $where = 'WHERE 1=1';
        $uWhere = 'WHERE 1=1';
        $posWhere = "";

        if (!empty($params['from_date']) && !empty($params['to_date'])) {
            $usWhere .= " AND DATE(us.created_at) BETWEEN :fromDate AND :toDate ";
            $posWhere .= "AND DATE(p.created_at) BETWEEN '".$params['from_date']."' AND '".$params['to_date']."'";
            $dbParams['fromDate'] = $params['from_date'];
            $dbParams['toDate'] = $params['to_date'];
        }

        if (!empty($params['technician']) && is_array($params['technician'])) {
            foreach ($params['technician'] as $key => $technician) {
                $where .= " AND labor.technician = :technician{$key} ";
                $uWhere .= " AND l.technician = '".$technician."' ";
                $dbParams["technician{$key}"] = $technician;
            }
        }

        $sql =
            "SELECT labor.actual_hours, labor.paid_hours, labor.billed_hours, labor.technician,
                    sales.*
            FROM dms_unit_sale_labor labor
            JOIN (
                /* unit sales */
                SELECT
                    us.id sale_id, i.id invoice_id, i.doc_num as doc_num, i.total invoice_total,
                    i.invoice_date sale_date, us.sales_person_id, c.display_name customer_name,

                    SUM(sales_units.sale_amount) unit_sale_amount,
                    SUM(sales_units.cost_amount) unit_cost_amount,

                    SUM(sales_parts.sale_amount) part_sale_amount,
                    SUM(sales_parts.cost_amount) part_cost_amount,

                    SUM(sales_labor.sale_amount) labor_sale_amount,
                    SUM(sales_labor.cost_amount) labor_cost_amount,
                    inventory.stock as inventory_stock,
                    inventory.manufacturer as inventory_make,
                    inventory.notes as inventory_notes

                FROM dms_unit_sale us
                LEFT JOIN dms_unit_sale_accessory usa ON usa.unit_sale_id=us.id
                LEFT JOIN dms_customer c ON us.buyer_id=c.id
                LEFT JOIN qb_invoices i ON i.unit_sale_id=us.id
                LEFT JOIN inventory ON inventory.inventory_id = us.inventory_id

                /* use this to prevent getting DP invoices */
                /* JOIN qb_invoice_items ii ON i.id=ii.invoice_id */

                LEFT JOIN (
                    SELECT
                       ii.invoice_id,
                       qb_invoices.total as sale_amount,
                       SUM(COALESCE(qi.cost, inv.true_cost, 0)) cost_amount
                    FROM qb_invoice_items ii
                    LEFT JOIN qb_items qi ON qi.id=ii.item_id
                    LEFT JOIN inventory inv ON qi.item_primary_id=inv.inventory_id
                    INNER JOIN
                        qb_invoices
                        ON qb_invoices.id = ii.invoice_id
                    WHERE qi.type = 'trailer' OR qi.type = 'deposit_down_payment'
                    GROUP BY ii.invoice_id
                ) sales_units ON i.id = sales_units.invoice_id

                LEFT JOIN (
                    SELECT
                       ii.invoice_id,
                       SUM(ii.unit_price * ii.qty) sale_amount,
                       SUM(COALESCE(qi.cost, pa.dealer_cost, 0)) cost_amount
                    FROM qb_invoice_items ii
                    LEFT JOIN qb_items qi ON qi.id=ii.item_id
                    LEFT JOIN parts_v1 pa ON qi.item_primary_id=pa.id
                    WHERE qi.type = 'part'
                    GROUP BY ii.invoice_id
                ) sales_parts ON i.id = sales_parts.invoice_id

                LEFT JOIN (
                    SELECT
                       ii.invoice_id,
                       SUM(ii.unit_price * ii.qty) sale_amount,
                       SUM(COALESCE(qi.cost, 0)) cost_amount
                    FROM qb_invoice_items ii
                    LEFT JOIN qb_items qi ON qi.id=ii.item_id
                    WHERE qi.type = 'labor'
                    GROUP BY ii.invoice_id
                ) sales_labor ON i.id = sales_labor.invoice_id

                WHERE us.dealer_id=:dealerId
                {$usWhere}
                GROUP BY us.id) sales ON labor.unit_sale_id=sales.sale_id
            {$where}";

        $union = "SELECT
                        l.actual_hours, l.paid_hours, l.billed_hours, l.technician,
                        NULL AS sale_id, i.id invoice_id, i.doc_num AS doc_num, i.total invoice_total,
                        i.invoice_date sale_date, i.sales_person_id, c.display_name customer_name,

                        0 AS unit_sale_amount,
                        0 AS unit_cost_amount,
                        0 AS part_sale_amount,
                        0 AS part_cost_amount,

                        SUM(l.quantity * l.unit_price) AS labor_sale_amount,
                        SUM(l.quantity * l.dealer_cost) AS labor_cost_amount,

                        NULL as inventory_stock,
                        NULL as inventory_make,
                        NULL as inventory_notes
                    FROM
                        qb_payment_labors l
                        JOIN qb_payment p ON l.payment_id = p.id
                        JOIN qb_invoices i ON p.invoice_id = i.id
                        JOIN dms_customer c ON i.customer_id = c.id
                    ".$uWhere." ".$posWhere." AND p.dealer_id = ".$params['dealer_id']."
                        GROUP BY l.technician, p.id";

        $sql .= " UNION ALL ";
        $sql .= $union;

        $result = DB::select($sql, $dbParams);

        $all = [];
        foreach ($result as $row) {
            $all[str_replace(" ","_",$row->technician)][] = (array)$row;
        }

        return $all;
    }
}
