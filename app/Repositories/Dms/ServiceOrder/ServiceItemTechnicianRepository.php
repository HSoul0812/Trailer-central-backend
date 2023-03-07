<?php


namespace App\Repositories\Dms\ServiceOrder;


use App\Models\CRM\Dms\ServiceOrder\ServiceItemTechnician;
use App\Repositories\RepositoryAbstract;
use App\Utilities\JsonApi\WithRequestQueryable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ServiceItemTechnicianRepository extends RepositoryAbstract implements ServiceItemTechnicianRepositoryInterface
{
    use WithRequestQueryable;

    private const STATUS_COMPLETE = 'completed';
    private const STATUS_INCOMPLETE = 'incomplete';
    
    private const COMPLETED_ON_TYPE_TECH = 'tech_completed_on';
    private const COMPLETED_ON_TYPE_RO = 'ro_completed_on';

    public function __construct(Builder $baseQuery)
    {
        $this->withQuery($baseQuery);
    }

    public function get($params)
    {
        return $this->query()->get();
    }

    public function findByLocation($locationId)
    {
        return $this->query()
            ->with(['serviceItem', 'serviceItem.serviceOrder'])
            ->whereHas('serviceItem.serviceOrder', function($query) use ($locationId) {
                $query->where('location', '=', $locationId);
            })
            ->get();
    }

    public function findByDealer($dealerId)
    {
        return $this->query()
            ->with(['serviceItem', 'serviceItem.serviceOrder'])
            ->whereHas('serviceItem.serviceOrder', function($query) use ($dealerId) {
                $query->where('dealer_id', '=', $dealerId);
            })
            ->get();
    }

    /**
     * Get Sales Report
     *
     * @param array $params
     * @return array
     */
    public function serviceReport($params) :array
    {
        $dbParams = [
            'dealerId' => $params['dealer_id'],
            'dealerId2' => $params['dealer_id'],
            'dealerId3' => $params['dealer_id'],
            'dealerId4' => $params['dealer_id'],
        ];
        $where = 'WHERE technician.dealer_id = :dealerId ';
        
        $closedAtField = 's_technician.completed_date';

        if (!empty($params['completed_on_type'])) {
            if ($params['completed_on_type'] === self::COMPLETED_ON_TYPE_RO) {
                $closedAtField = 'r_order.closed_at';
            }
        }

        if (!empty($params['from_date']) && !empty($params['to_date'])) {
            $where .= " AND DATE($closedAtField) BETWEEN :fromDate AND :toDate ";
            $dbParams['fromDate'] = $params['from_date'];
            $dbParams['toDate'] = $params['to_date'];
        }

        if (!empty($params['technician_id']) && is_array($params['technician_id'])) {
            foreach ($params['technician_id'] as $key => $technicianId) {
                $where .= " AND technician.id = :technicianId{$key} ";
                $dbParams["technicianId{$key}"] = $technicianId;
            }
        }

        if (!empty($params['repair_order_type']) && is_array($params['repair_order_type'])) {
            foreach ($params['repair_order_type'] as $key => $type) {
                $where .= " AND r_order.type = :rOrderType{$key} ";
                $dbParams["rOrderType{$key}"] = $type;
            }
        }

        if (!empty($params['repair_order_status'])) {
            if ($params['repair_order_status'] == self::STATUS_COMPLETE) {
                $where .= " AND r_order.closed_at IS NOT NULL ";
            } else if ($params['repair_order_status'] == self::STATUS_INCOMPLETE) {
                $where .= " AND r_order.closed_at IS NULL ";
            }
        }

        $sql =
            "SELECT technician.dealer_id, technician.id technician_id, technician.first_name, technician.last_name,
                    s_technician.act_hrs, s_technician.paid_hrs, s_technician.billed_hrs,
                    r_order.type repair_order_type, r_order.created_at ro_created_at, r_order.closed_at ro_completed_date,
                    s_item.amount paid_retail, r_order.user_defined_id ro_name,
                    sales.*
            FROM dms_settings_technician technician
            JOIN dms_service_technician AS s_technician ON technician.id = s_technician.dms_settings_technician_id
            JOIN dms_service_item AS s_item ON s_technician.service_item_id = s_item.id
            JOIN dms_repair_order AS r_order ON s_item.repair_order_id = r_order.id
            LEFT JOIN (
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
                    WHERE qi.dealer_id = :dealerId2
                        AND (qi.type = 'trailer' OR qi.type = 'deposit_down_payment')
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
                    WHERE qi.dealer_id = :dealerId3
                        AND qi.type = 'part'
                    GROUP BY ii.invoice_id
                ) sales_parts ON i.id = sales_parts.invoice_id

                LEFT JOIN (
                    SELECT
                       ii.invoice_id,
                       SUM(ii.unit_price * ii.qty) sale_amount,
                       SUM(COALESCE(qi.cost, 0)) cost_amount
                    FROM qb_invoice_items ii
                    LEFT JOIN qb_items qi ON qi.id=ii.item_id
                    WHERE qi.dealer_id = :dealerId4
                        AND qi.type = 'labor'
                    GROUP BY ii.invoice_id
                ) sales_labor ON i.id = sales_labor.invoice_id
                GROUP BY us.id) sales ON r_order.unit_sale_id=sales.sale_id
            {$where}";

        $result = DB::select($sql, $dbParams);

        $all = [];
        foreach ($result as $row) {
            $all[$row->technician_id][] = (array)$row;
        }

        return $all;
    }
}
