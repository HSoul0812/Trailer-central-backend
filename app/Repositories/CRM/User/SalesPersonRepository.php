<?php

namespace App\Repositories\CRM\User;

use App\Models\CRM\User\SalesPerson;
use App\Models\User\NewDealerUser;
use App\Repositories\RepositoryAbstract;
use App\Utilities\JsonApi\WithRequestQueryable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SalesPersonRepository extends RepositoryAbstract implements SalesPersonRepositoryInterface
{
    use WithRequestQueryable;

    public function __construct()
    {
        $this->withQuery(SalesPerson::query());
    }

    /**
     * find records; similar to findBy()
     * @param array $params
     * @return Collection<SalesPerson>
     */
    public function get($params)
    {
        $query = $this->query();

        // add other queryable params here
        $dealerId = $params['dealer_id'] ?? $this->requestQueryableRequest->input('dealer_id');
        if ($dealerId) {
            $newDealerUser = NewDealerUser::findOrFail($dealerId);
            $query = $query->WHERE('user_id', $newDealerUser->user_id);
        }

        return $query->get();
    }

    public function getAll($params) {
        $query = SalesPerson::SELECT('*');

        if (isset($params['dealer_id'])) {
            $newDealerUser = NewDealerUser::findOrFail($params['dealer_id']);
            $query = $query->WHERE('user_id', $newDealerUser->user_id);
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function salesReport($params)
    {
        $dbParams = ['dealerId1' => $params['dealer_id']];
        $dbParams['dealerId2'] = $params['dealer_id'];
        $dbParams['dealerId3'] = $params['dealer_id'];

        if (!empty($params['from_date']) && !empty($params['to_date'])) {
            $dateFromClause1 = "AND DATE(us.created_at) BETWEEN :fromDate1 AND :toDate1";
            $dateFromClause2 = "AND DATE(ps.created_at) BETWEEN :fromDate2 AND :toDate2";
            $dbParams['fromDate1'] = $dbParams['fromDate2'] = $params['from_date'];
            $dbParams['toDate1'] = $dbParams['toDate2'] = $params['to_date'];
        } else {
            $dateFromClause1 = "";
            $dateFromClause2 = "";
        }

        $sql =
            "SELECT sp.first_name, sp.last_name, sales.*
            FROM crm_sales_person sp
            JOIN (
                /* unit sales */
                SELECT us.id sale_id, i.id invoice_id, 'unit_sale' sale_type, us.created_at sale_date, us.sales_person_id, c.display_name customer_name,
                    SUM(us.subtotal) sale_amount, /* how much the item was sold, minus discounts */
                    SUM(ii.qty *
                        COALESCE(qi.cost, inv.price, usa.misc_dealer_cost, pa.dealer_cost)
                    ) cost_amount /* how much the item cost */
                FROM dms_unit_sale us
                LEFT JOIN dms_unit_sale_accessory usa ON usa.unit_sale_id=us.id
                LEFT JOIN dms_customer c ON us.buyer_id=c.id
                LEFT JOIN qb_invoices i ON i.unit_sale_id=us.id
                LEFT JOIN qb_invoice_items ii ON ii.invoice_id=i.id
                LEFT JOIN qb_items qi ON qi.id=ii.item_id
                LEFT JOIN inventory inv ON qi.item_primary_id=inv.inventory_id AND qi.type='trailer'
                LEFT JOIN parts_v1 pa ON qi.item_primary_id=pa.id AND qi.type='part'
                WHERE qi.type IN ('trailer', 'part')
                AND us.dealer_id=:dealerId1
                {$dateFromClause1}
                GROUP BY us.id

                UNION

                /* POS sales */
                SELECT ps.id sale_id, ps.id invoice_id, 'pos' sale_type, ps.created_at sale_date, ps.sales_person_id, c.display_name customer_name,
                    SUM(COALESCE(
                        psp.subtotal - ps.discount,
                        (psp.qty * psp.price) - ps.discount,
                        (psp.qty * i.unit_price) - ps.discount
                        )) sale_amount,
                    SUM(psp.qty * i.cost) cost_amount
                FROM crm_pos_sales ps
                JOIN crm_pos_sale_products psp ON psp.sale_id=ps.id
                JOIN dms_customer c ON ps.customer_id=c.id
                LEFT JOIN qb_items i ON i.id=psp.item_id
                JOIN crm_pos_register pr ON ps.register_id=pr.id
                JOIN crm_pos_outlet po ON pr.outlet_id=po.id
                WHERE i.type <> 'tax'
                AND po.dealer_id=:dealerId2
                {$dateFromClause2}
                GROUP BY ps.id) sales ON sales.sales_person_id=sp.id
            LEFT JOIN new_dealer_user ndu ON ndu.user_id=sp.user_id
            WHERE ndu.id=:dealerId3
            ORDER BY sales.sale_date DESC";

        $result = DB::select($sql, $dbParams);

        // organize by sales person
        $all = [];
        foreach ($result as $row) {
            $all[$row->sales_person_id][] = (array)$row;
        }

        return $all;
    }

}
