<?php

namespace App\Repositories\Dms\ServiceOrder;

use App\Repositories\RepositoryAbstract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class UnitSaleLaborRepository
 * @package App\Repositories\Dms\ServiceOrder
 */
class UnitSaleLaborRepository extends RepositoryAbstract implements UnitSaleLaborRepositoryInterface
{
    public function serviceReport($params): Collection
    {
        $dbParams = ['dealerId' => $params['dealer_id']];
        $usWhere = "";
        $technicianWhere = '';

        if (!empty($params['from_date']) && !empty($params['to_date'])) {
            $usWhere .= " AND DATE(us.created_at) BETWEEN :fromDate AND :toDate ";
            $dbParams['fromDate'] = $params['from_date'];
            $dbParams['toDate'] = $params['to_date'];
        }

        if (!empty($params['technician_id'])) {
            $technicianWhere .= " WHERE technician.id = :technicianId ";
            $dbParams['technicianId'] = $params['technician_id'];
        }

        $sql =
            "SELECT technician.id technician_id, technician.first_name, technician.last_name,
                    s_technician.act_hrs, s_technician.paid_hrs, s_technician.billed_hrs,
                    r_order.type repair_order_type,
                    sales.*
            FROM dms_settings_technician technician
            JOIN dms_service_technician AS s_technician ON technician.id = s_technician.dms_settings_technician_id
            JOIN dms_service_item AS s_item ON s_technician.service_item_id = s_item.id
            JOIN dms_repair_order AS r_order ON s_item.repair_order_id = r_order.id
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
                GROUP BY us.id) sales ON r_order.unit_sale_id=sales.sale_id
            {$technicianWhere}";

        $result = DB::select($sql, $dbParams);

        $all = [];
        foreach ($result as $row) {
            $all[$row->technician_id][] = (array)$row;
        }

        return $all;
    }
}
