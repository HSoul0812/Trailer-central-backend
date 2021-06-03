<?php

namespace App\Repositories\CRM\User;

use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\Integration\Auth\AccessToken;
use App\Models\User\NewDealerUser;
use App\Repositories\RepositoryAbstract;
use App\Utilities\JsonApi\WithRequestQueryable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SalesPersonRepository extends RepositoryAbstract implements SalesPersonRepositoryInterface
{
    use WithRequestQueryable;

    public const FILTER_COST_OVERHEAD = 'cost-and-overhead';
    public const FILTER_TRUE_TOTAL_COST = 'true-total-cost';

    public function __construct(Builder $baseQuery)
    {
        $this->withQuery($baseQuery);
    }

    /**
     * Create Sales Person
     *
     * @param array $params
     * @return SalesPerson
     */
    public function create($params) {
        return SalesPerson::create($params);
    }

    /**
     * Update Sales Person
     *
     * @param array $params
     * @return SalesPerson
     */
    public function update($params) {
        $salesPerson = SalesPerson::findOrFail($params['id']);

        DB::transaction(function() use (&$salesPerson, $params) {
            // Fill Text Details
            $salesPerson->fill($params)->save();
        });

        return $salesPerson;
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
            $query = $query->where('user_id', $newDealerUser->user_id);
        }

        if (isset($params['user_id'])) {
            $query = $query->where('user_id', $params['user_id']);
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
        $query = SalesPerson::select('*');

        if (isset($params['dealer_id'])) {
            $newDealerUser = NewDealerUser::findOrFail($params['dealer_id']);
            $query = $query->where('user_id', $newDealerUser->user_id);
        }

        if (isset($params['user_id'])) {
            $query = $query->where('user_id', $params['user_id']);
        }

        // Get Sales People With IMAP and/or Gmail Credentials
        if (!empty($params['has_imap'])) {
            // Require Sales Person ID NULL or 0
            $query = $query->where(function($query) {
                $query->whereNull(LeadStatus::getTableName().'.sales_person_id')
                      ->orWhere(LeadStatus::getTableName().'.sales_person_id', 0);
            })->where(Lead::getTableName().'.is_archived', 0)
              ->where(Lead::getTableName().'.is_spam', 0)
              ->whereRaw(Lead::getTableName().'.date_submitted > CURDATE() - INTERVAL 30 DAY');
            $query->where("imap_password IS NOT NULL AND imap_password <> ''");
            $query->where("imap_server IS NOT NULL AND imap_server <> ''");
            $query->where("imap_port IS NOT NULL AND imap_port <> ''");
            $query->where("(imap_failed IS NULL or imap_failed = 0)");
            $query->where("deleted_at IS NULL");
            $query = $query->where('user_id', $params['user_id']);
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * Get All Salespeople w/Imap Credentials
     *
     * @param int $userId
     * @return Collection of SalesPerson
     */
    public function getAllImap($userId) {
        return SalesPerson::select(SalesPerson::getTableName().'.*')
                                ->leftJoin(AccessToken::getTableName(), function($join) {
            $join->on(AccessToken::getTableName().'.relation_id', '=', SalesPerson::getTableName().'.id')
                 ->whereTokenType('google')
                 ->whereRelationType('sales_person');
        })->where('user_id', $userId)->where(function($query) {
            $query->whereNotNull(AccessToken::getTableName().'.id')
                  ->orWhere(function($query) {
                    $query->whereNotNull('imap_password')
                          ->where('imap_password', '<>', '')
                          ->whereNotNull('imap_server')
                          ->where('imap_server', '<>', '')
                          ->whereNotNull('imap_port')
                          ->where('imap_port', '<>', '');
            });
        })->get();
    }

    /**
     * Get By Smtp Email
     *
     * @param int $userId
     * @param string $email
     * @return null|SalesPerson
     */
    public function getBySmtpEmail(int $userId, string $email): ?SalesPerson {
        // Get SMTP By Dealer ID and SMTP Email
        return SalesPerson::where('user_id', $userId)->where('smtp_email', $email)->first();
    }


    /**
     * Get Sales Report
     *
     * @param array $params
     * @return array
     */
    public function salesReport($params): array
    {
        $dbParams = ['dealerId1' => $params['dealer_id']];
        $dbParams['dealerId2'] = $params['dealer_id'];
        $dbParams['dealerId3'] = $params['dealer_id'];
        $dbParams['dealerId4'] = $params['dealer_id'];
        $dbParams['dealerId5'] = $params['dealer_id'];

        $roFilters = ' AND ((dms_unit_sale.total_price - payments.paid_amount) <= 0)';
        $quotesFilters = ' AND ((us.total_price - payments.paid_amount) <= 0)';
        $quotesJoins = "LEFT JOIN dms_repair_order ON dms_repair_order.unit_sale_id = us.id";
        $partsPullFrom = " sales_parts ON i.id = sales_parts.invoice_id";
        $laborPullFrom = " sales_labor ON i.id = sales_labor.invoice_id";

        if (isset($params['filterMode']) && $params['filterMode'] === self::FILTER_TRUE_TOTAL_COST) {
            $roFilters = " AND (((dms_unit_sale.total_price - payments.paid_amount) <= 0) AND dms_repair_order.type = 'internal')";
            $quotesJoins = "INNER JOIN dms_repair_order ON dms_repair_order.unit_sale_id = us.id";
            $quotesJoins .= " LEFT JOIN qb_invoices repair_order_invoice ON repair_order_invoice.repair_order_id = dms_repair_order.id";
            $partsPullFrom = " sales_parts ON repair_order_invoice.id = sales_parts.invoice_id";
            $laborPullFrom = " sales_labor ON repair_order_invoice.id = sales_labor.invoice_id";
        }

        $dateFromClause1 = ""; // no date filters

        if (!empty($params['from_date']) && !empty($params['to_date'])) {
            $dateFromClause1 = "AND DATE(us.created_at) BETWEEN :fromDate1 AND :toDate1";
            $dbParams['fromDate1'] = $dbParams['fromDate2'] = $dbParams['fromDate3'] = $dbParams['fromDate4'] = $params['from_date'];
            $dbParams['toDate1'] = $dbParams['toDate2'] = $dbParams['toDate3'] = $dbParams['toDate4'] = $params['to_date'];
        }

        $sql = <<<SQL
            SELECT sp.first_name, sp.last_name, sales.*
            FROM crm_sales_person sp
            JOIN (
                /* unit sales */
                SELECT
                    us.id sale_id, i.id invoice_id, i.doc_num as doc_num, i.total invoice_total,
                    (CASE WHEN((us.total_price - payments.paid_amount) <= 0) THEN 'unit_sale_completed'
                        WHEN(i.repair_order_id IS NOT NULL) THEN 'repair_order'
                        ELSE 'pos' /* todo must have a clear marker in invoice that it is a pos sale */
                        END
                    ) sale_type,
                    i.invoice_date sale_date, us.sales_person_id, c.display_name customer_name,

                    (us.total_price - payments.paid_amount) remaining,

                    SUM(sales_units.cost_overhead) cost_overhead,
                    SUM(sales_units.true_total_cost) true_total_cost,

                    SUM(sales_units.sale_amount) unit_sale_amount,
                    SUM(sales_units.cost_amount) unit_cost_amount,

                    SUM(sales_parts.sale_amount) part_sale_amount,
                    SUM(sales_parts.cost_amount) part_cost_amount,

                    SUM(sales_labor.sale_amount) labor_sale_amount,
                    SUM(sales_labor.cost_amount) labor_cost_amount,

                    SUM(sales_units.sale_amount) AS retail_price,
                    SUM(inventory_discount.amount) AS retail_discount,

                    inventory.stock as inventory_stock,
                    inventory.manufacturer as inventory_make,
                    inventory.notes as inventory_notes

                FROM dms_unit_sale us
                LEFT JOIN dms_unit_sale_accessory usa ON usa.unit_sale_id=us.id
                LEFT JOIN dms_customer c ON us.buyer_id=c.id
                LEFT JOIN qb_invoices i ON i.unit_sale_id=us.id
                LEFT JOIN inventory ON inventory.inventory_id = us.inventory_id
                {$quotesJoins}

                /* use this to prevent getting DP invoices */
                /* JOIN qb_invoice_items ii ON i.id=ii.invoice_id */

                LEFT JOIN (
                    SELECT
                    qb_invoices.unit_sale_id,
                    SUM(COALESCE(qb_invoices.total, 0)) paid_amount
                    FROM qb_invoices
                    GROUP BY qb_invoices.unit_sale_id
                ) payments ON us.id = payments.unit_sale_id

                JOIN (
                    SELECT
                       ii.invoice_id,
                       SUM(ii.unit_price) as sale_amount,
                       SUM(COALESCE(qi.cost, inv.true_cost, 0)) cost_amount,
                       SUM(iii.cost_overhead) AS cost_overhead,
                       SUM(iii.true_total_cost) AS true_total_cost
                    FROM qb_invoice_items ii
                    LEFT JOIN qb_invoice_item_inventories iii ON iii.invoice_item_id = ii.id
                    LEFT JOIN qb_items qi ON qi.id=ii.item_id
                    LEFT JOIN inventory inv ON qi.item_primary_id=inv.inventory_id
                    INNER JOIN
                        qb_invoices
                        ON qb_invoices.id = ii.invoice_id
                    WHERE qi.type = 'trailer'
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
                ) {$partsPullFrom}

                LEFT JOIN (
                    SELECT
                       ii.invoice_id,
                       SUM(ii.unit_price * ii.qty) sale_amount,
                       SUM(COALESCE(qi.cost, 0)) cost_amount
                    FROM qb_invoice_items ii
                    LEFT JOIN qb_items qi ON qi.id=ii.item_id
                    WHERE qi.type = 'labor'
                    GROUP BY ii.invoice_id
                ) {$laborPullFrom}

                LEFT JOIN (
                    SELECT ii.invoice_id, SUM(ii.unit_price * -1) as amount
                    FROM qb_invoice_items ii
                    JOIN qb_items i ON ii.item_id = i.id AND i.type = 'discount' AND i.name = 'Inventory Discount'
                    GROUP BY ii.invoice_id
                ) inventory_discount ON i.id = inventory_discount.invoice_id

                WHERE us.dealer_id=:dealerId1
                {$dateFromClause1} {$quotesFilters}
                GROUP BY us.id
                HAVING remaining <= 0 -- Only be shown those records totally paid

                UNION

                /* POS sales via crm_pos_sales */
                SELECT ps.id sale_id, ps.id invoice_id, ps.id doc_num, ps.total ,'pos' sale_type,
                    ps.created_at sale_date, ps.sales_person_id, c.display_name customer_name,

                    (ps.total - ps.amount_received) remaining,

                    0 cost_overhead,  -- backward compatibility
                    0 true_total_cost, -- backward compatibility

                    SUM(sales_unit.sale_amount) as unit_sale_amount,
                    SUM(sales_unit.cost_amount) as unit_cost_amount,

                    SUM(sales_part.sale_amount) as part_sale_amount,
                    SUM(sales_part.cost_amount) as part_cost_amount,

                    SUM(sales_labor.sale_amount) as labor_sale_amount,
                    SUM(sales_labor.cost_amount) as labor_cost_amount,

                    SUM(sales_unit.sale_amount) AS retail_price,
                    SUM(inventory_discount.amount) AS retail_discount,

                    NULL as inventory_stock,
                    NULL as inventory_make,
                    NULL as inventory_notes

                FROM crm_pos_sales ps
                    LEFT JOIN dms_customer c ON ps.customer_id=c.id
                    LEFT JOIN crm_pos_register pr ON ps.register_id=pr.id
                    LEFT JOIN crm_pos_outlet po ON pr.outlet_id=po.id
                    LEFT JOIN (
                        SELECT
                            psp.sale_id,
                            SUM(psp.subtotal) sale_amount,
                            SUM(COALESCE(qi.cost, 0)) cost_amount
                        FROM crm_pos_sale_products psp
                        LEFT JOIN qb_items qi ON qi.id=psp.item_id
                        WHERE qi.type = 'part'
                        GROUP BY psp.sale_id
                    ) sales_part ON ps.id = sales_part.sale_id

                    JOIN (
                        SELECT
                            psp.sale_id,
                            SUM(psp.subtotal) sale_amount,
                            SUM(COALESCE(qi.cost, 0)) cost_amount
                        FROM crm_pos_sale_products psp
                        LEFT JOIN qb_items qi ON qi.id=psp.item_id
                        WHERE qi.type = 'trailer'
                        GROUP BY psp.sale_id
                    ) sales_unit ON ps.id = sales_unit.sale_id

                    LEFT JOIN (
                        SELECT
                            psp.sale_id,
                            SUM(psp.subtotal) amount
                        FROM crm_pos_sale_products psp
                        JOIN qb_items qi ON qi.id=psp.item_id AND qi.type = 'discount' AND qi.name = 'Inventory Discount'
                        GROUP BY psp.sale_id
                    ) inventory_discount ON ps.id = inventory_discount.sale_id

                    LEFT JOIN (
                        SELECT
                            psp.sale_id,
                            SUM(psp.subtotal) sale_amount,
                            SUM(COALESCE(qi.cost, 0)) cost_amount
                        FROM crm_pos_sale_products psp
                        LEFT JOIN qb_items qi ON qi.id=psp.item_id
                        WHERE qi.type = 'labor'
                        GROUP BY psp.sale_id
                    ) sales_labor ON ps.id = sales_labor.sale_id

                WHERE po.dealer_id=:dealerId2 AND DATE(ps.created_at) BETWEEN :fromDate2 AND :toDate2
                GROUP BY ps.id
                HAVING remaining <= 0 -- Only be shown those records totally paid

              UNION

            /* POS sales via qb_invoices */
                SELECT qb_invoices.id sale_id, qb_invoices.id invoice_id, qb_invoices.doc_num doc_num,
                    qb_invoices.total total ,'pos' sale_type, qb_invoices.invoice_date sale_date,
                    qb_invoices.sales_person_id, c.display_name customer_name,

                    (qb_invoices.total - SUM(payments.paid_amount)) remaining,

                    SUM(sales_unit.cost_overhead)                  cost_overhead,
                    SUM(sales_unit.true_total_cost)                true_total_cost,

                    SUM(sales_unit.sale_amount) as unit_sale_amount,
                    SUM(sales_unit.cost_amount) as unit_cost_amount,

                    SUM(sales_part.sale_amount) as part_sale_amount,
                    SUM(sales_part.cost_amount) as part_cost_amount,

                    SUM(sales_labor.sale_amount) as labor_sale_amount,
                    SUM(sales_labor.cost_amount) as labor_cost_amount,

                    SUM(sales_unit.sale_amount) AS retail_price,
                    SUM(inventory_discount.amount) AS retail_discount,

                    NULL as inventory_stock,
                    NULL as inventory_make,
                    NULL as inventory_notes

                FROM qb_invoices
                    LEFT JOIN dms_customer c ON qb_invoices.customer_id=c.id
                    LEFT JOIN (
                        SELECT
                            psp.invoice_id,
                            SUM(psp.unit_price) sale_amount,
                            SUM(COALESCE(qi.cost, 0)) cost_amount
                        FROM qb_invoice_items psp
                        LEFT JOIN qb_items qi ON qi.id=psp.item_id
                        WHERE qi.type = 'part'
                        GROUP BY psp.invoice_id
                    ) sales_part ON qb_invoices.id = sales_part.invoice_id

                    JOIN (
                        SELECT
                            psp.invoice_id,
                            SUM(psp.unit_price) sale_amount,
                            SUM(COALESCE(qi.cost, 0)) cost_amount,
                            SUM(iii.cost_overhead) AS cost_overhead,
                            SUM(iii.true_total_cost) AS true_total_cost
                        FROM qb_invoice_items psp
                        LEFT JOIN qb_invoice_item_inventories iii ON iii.invoice_item_id = psp.id
                        LEFT JOIN qb_items qi ON qi.id=psp.item_id
                        WHERE qi.type = 'trailer'
                        GROUP BY psp.invoice_id
                    ) sales_unit ON qb_invoices.id = sales_unit.invoice_id

                    LEFT JOIN (
                        SELECT
                        ii.invoice_id,SUM(ii.unit_price) amount
                        FROM qb_invoice_items ii
                        LEFT JOIN qb_items qi ON qi.id=ii.item_id AND qi.type = 'discount' AND qi.name = 'Inventory Discount'
                        GROUP BY ii.invoice_id
                    ) inventory_discount ON qb_invoices.id = inventory_discount.invoice_id

                    LEFT JOIN (
                        SELECT
                            psp.invoice_id,
                            SUM(psp.unit_price) sale_amount,
                            SUM(COALESCE(qi.cost, 0)) cost_amount
                        FROM qb_invoice_items psp
                        LEFT JOIN qb_items qi ON qi.id=psp.item_id
                        WHERE qi.type = 'labor'
                        GROUP BY psp.invoice_id
                    ) sales_labor ON qb_invoices.id = sales_labor.invoice_id

                    LEFT JOIN (
                        SELECT
                           qb_payment.invoice_id,
                           SUM(COALESCE(qb_payment.amount, 0)) paid_amount
                        FROM qb_payment
                        GROUP BY qb_payment.invoice_id
                    ) payments ON qb_invoices.id = payments.invoice_id

                WHERE qb_invoices.dealer_id= :dealerId4
                AND DATE(qb_invoices.invoice_date) BETWEEN :fromDate3 AND :toDate3 AND qb_invoices.unit_sale_id IS NULL
                AND qb_invoices.repair_order_id IS NULL
                GROUP BY qb_invoices.id
                HAVING remaining <= 0 -- Only be shown those records totally paid

                UNION

            /* RO sales */

                SELECT qb_invoices.id sale_id, qb_invoices.id invoice_id, qb_invoices.doc_num doc_num,
                    qb_invoices.total total ,'RO' sale_type, qb_invoices.invoice_date sale_date,
                    qb_invoices.sales_person_id, c.display_name customer_name,

                    (qb_invoices.total - SUM(payments.paid_amount)) remaining,

                    SUM(sales_unit.cost_overhead) cost_overhead,
                    SUM(sales_unit.true_total_cost) true_total_cost,

                    SUM(sales_unit.sale_amount) as unit_sale_amount,
                    SUM(sales_unit.cost_amount) as unit_cost_amount,

                    SUM(sales_part.sale_amount) as part_sale_amount,
                    SUM(sales_part.cost_amount) as part_cost_amount,

                    SUM(sales_labor.sale_amount) as labor_sale_amount,
                    SUM(sales_labor.cost_amount) as labor_cost_amount,

                    SUM(sales_unit.sale_amount) AS retail_price,
                    SUM(inventory_discount.amount) AS retail_discount,

                    NULL as inventory_stock,
                    NULL as inventory_make,
                    NULL as inventory_notes

                FROM qb_invoices
                    LEFT JOIN dms_repair_order ON qb_invoices.repair_order_id = dms_repair_order.id
                    LEFT JOIN dms_unit_sale ON dms_repair_order.unit_sale_id = dms_unit_sale.id
                    LEFT JOIN qb_invoices unit_sale_invoice ON dms_unit_sale.id = unit_sale_invoice.unit_sale_id
                    LEFT JOIN dms_customer c ON qb_invoices.customer_id=c.id
                    LEFT JOIN (
                        SELECT
                            psp.invoice_id,
                            SUM(psp.unit_price) sale_amount,
                            SUM(COALESCE(qi.cost, 0)) cost_amount
                        FROM qb_invoice_items psp
                        LEFT JOIN qb_items qi ON qi.id=psp.item_id
                        WHERE qi.type = 'part'
                        GROUP BY psp.invoice_id
                    ) sales_part ON qb_invoices.id = sales_part.invoice_id

                    LEFT JOIN (
                        SELECT
                           qb_payment.invoice_id,
                           SUM(COALESCE(qb_payment.amount, 0)) paid_amount
                        FROM qb_payment
                        GROUP BY qb_payment.invoice_id
                    ) payments ON unit_sale_invoice.id = payments.invoice_id

                    JOIN (
                        SELECT
                            psp.invoice_id,
                            SUM(psp.unit_price) sale_amount,
                            SUM(COALESCE(qi.cost, 0)) cost_amount,
                            SUM(iii.cost_overhead) AS cost_overhead,
                            SUM(iii.true_total_cost) AS true_total_cost
                        FROM qb_invoice_items psp
                        LEFT JOIN qb_invoice_item_inventories iii ON iii.invoice_item_id = psp.id
                        LEFT JOIN qb_items qi ON qi.id=psp.item_id
                        WHERE qi.type = 'trailer'
                        GROUP BY psp.invoice_id
                    ) sales_unit ON qb_invoices.id = sales_unit.invoice_id

                    LEFT JOIN (
                        SELECT
                            ii.invoice_id,
                            SUM(ii.unit_price * -1) amount
                        FROM qb_invoice_items ii
                        LEFT JOIN qb_items qi ON qi.id=ii.item_id AND qi.type = 'discount' AND qi.name = 'Inventory Discount'
                        GROUP BY ii.invoice_id
                    ) inventory_discount ON qb_invoices.id = inventory_discount.invoice_id

                    LEFT JOIN (
                        SELECT
                            psp.invoice_id,
                            SUM(psp.unit_price) sale_amount,
                            SUM(COALESCE(qi.cost, 0)) cost_amount
                        FROM qb_invoice_items psp
                        LEFT JOIN qb_items qi ON qi.id=psp.item_id
                        WHERE qi.type = 'labor'
                        GROUP BY psp.invoice_id
                    ) sales_labor ON qb_invoices.id = sales_labor.invoice_id

                WHERE qb_invoices.dealer_id= :dealerId5
                AND DATE(qb_invoices.invoice_date) BETWEEN :fromDate4 AND :toDate4 AND qb_invoices.unit_sale_id IS NULL
                AND qb_invoices.repair_order_id IS NOT NULL AND dms_repair_order.unit_sale_id IS NOT NULL $roFilters

                GROUP BY qb_invoices.id
                HAVING remaining <=0 -- Only be shown those records totally paid

            ) sales ON sales.sales_person_id=sp.id

            LEFT JOIN new_dealer_user ndu ON ndu.user_id=sp.user_id
            WHERE ndu.id=:dealerId3 AND sp.deleted_at IS NULL
SQL;

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
        $query = SalesPerson::select(SalesPerson::getTableName() . '.*')
                            ->leftJoin(LeadStatus::getTableName(), LeadStatus::getTableName() . '.sales_person_id', '=', SalesPerson::getTableName() . '.id')
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
        return $query->first();
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
    public function roundRobinSalesPerson($dealerId, $dealerLocationId, $salesType, $newestSalesPerson) {
        // Set Newest ID
        $newestSalesPersonId = 0;
        if(!empty($newestSalesPerson->id)) {
            $newestSalesPersonId = $newestSalesPerson->id;
        }

        $nextSalesPerson = null;
        $lastId = 0;
        $dealerLocationId = (int) $dealerLocationId;

        $salesPeople = $this->getSalesPeopleBy($dealerId, $dealerLocationId, $salesType);

        // Loop Valid Sales People
        if(count($salesPeople) > 1) {
            $lastSalesPerson = $salesPeople->last();
            $lastId = $lastSalesPerson->id;
            foreach($salesPeople as $salesPerson) {
                // Compare ID
                if($lastId === $newestSalesPersonId || $newestSalesPersonId === 0) {
                    $nextSalesPerson = $salesPerson;
                    break;
                }
                $lastId = $salesPerson->id;
            }

            // Still No Next Sales Person?
            if(empty($nextSalesPerson)) {
                $salesPerson = $salesPeople->first();
                $nextSalesPerson = $salesPerson;
            }
        } elseif(count($salesPeople) === 1) {
            $nextSalesPerson = $salesPeople->first();
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
    public function getSalesPeopleBy($dealerId, $dealerLocationId = null, $salesType = null) {
        // Get New Sales People By Dealer ID
        $newDealerUser = NewDealerUser::findOrFail($dealerId);
        $query = SalesPerson::select('*')->where('user_id', $newDealerUser->user_id);

        if ($dealerLocationId) {
            $query->where('dealer_location_id', $dealerLocationId);
        }

        if ($salesType) {
            $query->where("is_{$salesType}", 1);
        }

        return $query->get();

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
