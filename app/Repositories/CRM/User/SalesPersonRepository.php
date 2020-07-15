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

    /**
     * @var Array
     */
    private $salesPeople = [];

    /**
     * @var Array
     */
    private $lastSalesPeople = [];

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
     * @param int
     */
    public function findNewestSalesPerson($dealerId, $dealerLocationId, $salesType) {
        // Last Sales Person Already Exists?
        if(isset($this->lastSalesPeople[$dealerId][$dealerLocationId][$salesType])) {
            $newestSalesPersonId = $this->lastSalesPeople[$dealerId][$dealerLocationId][$salesType];
            return $this->findSalesPerson($newestSalesPersonId);
        }

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
            $salesPerson = new stdclass;
            $salesPerson->id = $salesPersonId;
        }

        // Set Sales Person ID
        $this->setLastSalesPerson($dealerId, $dealerLocationId, $salesType, $salesPersonId);

        // Return Sales Person
        return $salesPerson;
    }

    /**
     * Find Next Sales Person
     * 
     * @param int $dealerId
     * @param int $dealerLocationId
     * @param string $salesType
     * @param SalesPerson $newestSalesPerson
     * @param array $salesPeople
     * @return SalesPerson next sales person
     */
    public function findNextSalesPerson($dealerId, $dealerLocationId, $salesType, $newestSalesPerson) {
        // Set Newest ID
        $newestSalesPersonId = 0;
        if(!empty($newestSalesPerson->id)) {
            $newestSalesPersonId = $newestSalesPerson->id;
        }

        // Get Sales People for Dealer ID
        $salesPeople = $this->findSalesPeople($dealerId);

        // Loop Sales People
        $validSalesPeople = [];
        $nextSalesPerson = null;
        $lastId = 0;
        foreach($salesPeople as $k => $salesPerson) {
            // Search By Location?
            if($dealerLocationId !== 0 && $dealerLocationId !== '0') {
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
            $salesPerson = end($validSalesPeople);
            $lastId = $salesPerson->id;
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
            $salesPerson = reset($validSalesPeople);
            $nextSalesPerson = $salesPerson;
        }

        // Still No Next Sales Person?
        if(empty($nextSalesPerson)) {
            $nextSalesPerson = $newestSalesPerson;
        }

        // Set Next Salesperson
        $this->setLastSalesperson($dealerId, $dealerLocationId, $salesType, $nextSalesPerson->id);

        // Return Next Sales Person
        return $nextSalesPerson;
    }

    /**
     * Set Last Sales Person
     * 
     * @param int $dealerId
     * @param int $dealerLocationId
     * @param string $salesType
     * @param int $salesPersonId
     * @return int last sales person ID
     */
    public function setLastSalesPerson($dealerId, $dealerLocationId, $salesType, $salesPersonId) {
        // Assign to Arrays
        if(!isset($this->lastSalesPeople[$dealerId])) {
            $this->lastSalesPeople[$dealerId] = array();
        }

        // Match By Dealer Location ID!
        if(!empty($dealerLocationId)) {
            if(!isset($this->lastSalesPeople[$dealerId][$dealerLocationId])) {
                $this->lastSalesPeople[$dealerId][$dealerLocationId] = array();
            }
            $this->lastSalesPeople[$dealerId][$dealerLocationId][$salesType] = $salesPersonId;
        }

        // Always Set for 0!
        if(!isset($this->lastSalesPeople[$dealerId][0])) {
            $this->lastSalesPeople[$dealerId][0] = array();
        }
        $this->lastSalesPeople[$dealerId][0][$salesType] = $salesPersonId;

        // Return Last Sales Person ID
        return $this->lastSalesPeople[$dealerId][0][$salesType];
    }

    /**
     * Set Current Sales People
     * 
     * @param int $dealerId
     * @param array $salesPeople
     */
    public function setSalesPeople($dealerId, $salesPeople) {
        // Set Sales People for Dealer ID
        $this->salesPeople[$dealerId] = $salesPeople;

        // Return Current Sales People Array
        return $salesPeople;
    }

    /**
     * Find Sales People By Dealer ID
     * 
     * @param type $dealerId
     */
    public function findSalesPeople($dealerId) {
        // Already Exists?!
        if(isset($this->salesPeople[$dealerId])) {
            return $this->salesPeople[$dealerId];
        }

        // Get New Sales People By Dealer ID
        $newDealerUser = NewDealerUser::findOrFail($dealerId);
        $salesPeople = SalesPerson::select('*')
                                  ->where('user_id', $newDealerUser->user_id)
                                  ->orderBy('id', 'asc')->all();

        // Set Sales People
        $this->salesPeople = array(
            'dealerId' => $salesPeople
        );

        // Return
        return $salesPeople;
    }

    /**
     * Find Sales Person
     * 
     * @param int $salesPersonId
     */
    public function findSalesPerson($salesPersonId) {
        // Find Existing Sales People
        if(count($this->salesPeople) > 0) {
            $salesPeople = reset($this->salesPeople);
        }

        // Find Sales Person in Current Array
        $chosenSalesPerson = null;
        if(count($salesPeople) > 0) {
            foreach($salesPeople as $salesPerson) {
                if($salesPerson->id === $salesPersonId) {
                    $chosenSalesPerson = $salesPerson;
                    break;
                }
            }
        }

        // Still Can't Find?!
        if(empty($chosenSalesPerson)) {
            $chosenSalesPerson = SalesPerson::find($salesPersonId);
        }

        // Return!
        return $chosenSalesPerson;
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

        // Not a Valid Type? Set Default!
        if(!in_array($leadType, SalesPerson::TYPES_VALID)) {
            $salesType = 'default';
        }

        // Return Lead Type!
        return $salesType;
    }
}
