<?php

namespace App\Repositories\CRM\User;

use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
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
    
    public function create($params) {
        throw NotImplementedException;
    }

    /**
     * find records; similar to findBy()
     * @param array $params
     * @return Collection<SalesPerson>
     */
    public function get($params)
    {
        // Get Single!
        if(isset($params['sales_person_id'])) {
            return SalesPerson::find($params['sales_person_id']);
        }

        $query = $this->query();

        // add other queryable params here
        $dealerId = $params['dealer_id'] ?? $this->requestQueryableRequest->input('dealer_id');
        if ($dealerId) {
            $newDealerUser = NewDealerUser::findOrFail($dealerId);
            $query = $query->WHERE('user_id', $newDealerUser->user_id);
        }

        return $query->get();
    }

    /**
     * Get All Salespeople
     * 
     * @param int $params
     * @return type
     */
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

    

    /**
     * Find Newest Sales Person From Vars or Check DB
     * 
     * @param int $dealerId
     * @param int $dealerLocationId
     * @param string $salesType
     * @param SalesPerson
     */
    public function findNewestSalesPerson($dealerId, $dealerLocationId, $salesType) {
        // Find Newest Salesperson in DB
        $query = LeadStatus::select(SalesPerson::getTableName() . '.*')
                            ->leftJoin(SalesPerson::getTableName(), SalesPerson::getTableName() . '.id', '=', LeadStatus::getTableName() . '.sales_person_id')
                            ->leftJoin(Lead::getTableName(), Lead::getTableName() . '.identifier', '=', LeadStatus::getTableName() . '.tc_lead_identifier')
                            ->where(Lead::getTableName() . '.dealer_id', $dealerId)
                            ->where(SalesPerson::getTableName() . '.is_' . $salesType, 1)
                            ->where(SalesPerson::getTableName() . '.id', '<>', 0)
                            ->where(SalesPerson::getTableName() . '.id', '<>', '')
                            ->whereNotNull(SalesPerson::getTableName() . '.id')
                            ->orderBy(Lead::getTableName() . '.date_submitted', 'DESC');

        // Append Dealer Location
        if(!empty($dealerLocationId)) {
            $query = $query->where(SalesPerson::getTableName() . '.dealer_location_id', $dealerLocationId);
        }

        // Get Sales Person ID
        $salesPerson = $query->first();
        $salesPersonId = 0;
        if(!empty($salesPerson->id)) {
            $salesPersonId = $salesPerson->id;
        } else {
            $salesPerson = new \stdclass;
            $salesPerson->id = $salesPersonId;
        }

        // Return Sales Person
        return $salesPerson;
    }

    /**
     * Round Robin to Next Sales Person
     * 
     * @param int $dealerId
     * @param int $dealerLocationId
     * @param string $salesType
     * @param SalesPerson $newestSalesPerson
     * @param array $salesPeople
     * @return SalesPerson next sales person
     */
    public function roundRobinSalesPerson($dealerId, $dealerLocationId, $salesType, $newestSalesPerson, $salesPeople = array()) {
        // Set Newest ID
        $newestSalesPersonId = 0;
        if(!empty($newestSalesPerson->id)) {
            $newestSalesPersonId = $newestSalesPerson->id;
        }

        // Get Sales People for Dealer ID
        if(empty($salesPeople)) {
            $salesPeople = $this->findSalesPeople($dealerId);
        }

        // Loop Sales People
        $validSalesPeople = [];
        $nextSalesPerson = null;
        $lastId = 0;
        $dealerLocationId = (int) $dealerLocationId;
        foreach($salesPeople as $k => $salesPerson) {
            // Search By Location?
            if($dealerLocationId !== 0) {
                if($dealerLocationId !== $salesPerson->dealer_location_id) {
                    continue;
                }
            }

            // Search by Type?
            if($salesPerson->{'is_' . $salesType} !== 1 && $salesPerson->{'is_' . $salesType} !== '1') {
                continue;
            }

            // Insert Valid Salespeople
            $validSalesPeople[] = $salesPerson;
        }

        // Loop Valid Sales People
        if(count($validSalesPeople) > 1) {
            $lastSalesPerson = end($validSalesPeople);
            $lastId = $lastSalesPerson->id;
            foreach($validSalesPeople as $salesPerson) {
                // Compare ID
                if($lastId === $newestSalesPersonId || $newestSalesPersonId === 0) {
                    $nextSalesPerson = $salesPerson;
                    break;
                }
                $lastId = $salesPerson->id;
            }

            // Still No Next Sales Person?
            if(empty($nextSalesPerson)) {
                $salesPerson = reset($validSalesPeople);
                $nextSalesPerson = $salesPerson;
            }
        } elseif(count($validSalesPeople) === 1) {
            $nextSalesPerson = reset($validSalesPeople);
        }

        // Still No Next Sales Person?
        if(empty($nextSalesPerson)) {
            $nextSalesPerson = $newestSalesPerson;
        }

        // Return Next Sales Person
        return $nextSalesPerson;
    }

    /**
     * Find Sales People By Dealer ID
     * 
     * @param type $dealerId
     */
    public function findSalesPeople($dealerId) {
        // Get New Sales People By Dealer ID
        $newDealerUser = NewDealerUser::findOrFail($dealerId);
        return SalesPerson::select('*')
                          ->where('user_id', $newDealerUser->user_id)
                          ->orderBy('id', 'asc')->get();
    }

    /**
     * Find Sales Person Type
     * 
     * @param string $leadType
     * @return string
     */
    public function findSalesType($leadType) {
        // Set Default Lead Type
        $salesType = 'default';
        if(in_array($leadType, SalesPerson::TYPES_DEFAULT) || empty($leadType)) {
            $salesType = 'default';
        }

        // Set Inventory Lead Type
        if(in_array($leadType, SalesPerson::TYPES_INVENTORY)) {
            $salesType = 'inventory';
        }

        // Set To Valid Type if Exists!
        if(in_array($leadType, SalesPerson::TYPES_VALID)) {
            $salesType = $leadType;
        }
        // Not a Valid Type? Set Default!
        else {
            $salesType = 'default';
        }

        // Return Lead Type!
        return $salesType;
    }
}
