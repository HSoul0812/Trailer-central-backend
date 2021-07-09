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
            // Fill Sales Person Details
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
        $dbParams = [
            'dealerId1' => $params['dealer_id'],
            'dealerId2' => $params['dealer_id']
        ];

        $dateClause1 = ''; // no date filters
        $dateClause2 = ''; // no date filters

        $salesPersonClause1 = ''; // no date filters
        $salesPersonClause2 = ''; // no date filters

        if (!empty($params['salesperson'])) {
            $salesPersonClause1 = 'AND ps.sales_person_id = :salesPerson1';
            $salesPersonClause2 = 'AND IF(i.unit_sale_id IS NOT NULL, us.sales_person_id, i.sales_person_id) = :salesPerson2';

            $dbParams['salesPerson1'] = $params['salesperson'];
            $dbParams['salesPerson2'] = $params['salesperson'];
        }
        if (!empty($params['from_date']) && !empty($params['to_date'])) {
            $dateClause1 = 'AND DATE(ps.created_at) BETWEEN :fromDate1 AND :toDate1';
            $dateClause2 = 'AND DATE(i.invoice_date) BETWEEN :fromDate2 AND :toDate2';

            $dbParams['fromDate1'] = $params['from_date'];
            $dbParams['fromDate2'] = $params['from_date'];
            $dbParams['toDate1'] =  $params['to_date'];
            $dbParams['toDate2'] = $params['to_date'];
        }

        $sql = <<<SQL
            SELECT sp.first_name, sp.last_name, sales.*
            FROM crm_sales_person sp
            JOIN (
                /* POS sales via `crm_pos_sales` (legacy) for backward compatibility */
                SELECT ps.id                           AS sale_id,
                       ps.id                           AS invoice_id,
                       ps.id                           AS doc_num,
                       ps.total,
                       'pos'                           AS sale_type,
                       ps.created_at                   AS sale_date,
                       ps.sales_person_id,
                       c.display_name                  AS customer_name,

                       (ps.total - ps.amount_received) AS remaining,

                       SUM(sales_unit.cost_amount)     AS cost_overhead,   -- backward compatibility
                       SUM(sales_unit.cost_amount)     AS true_total_cost, -- backward compatibility

                       SUM(sales_unit.sale_amount)     AS unit_sale_amount,
                       SUM(sales_unit.cost_amount)     AS unit_cost_amount,

                       SUM(sales_unit.sale_amount)     AS retail_price,
                       SUM(inventory_discount.amount)  AS retail_discount,

                       NULL                            AS inventory_stock,
                       NULL                            AS inventory_make,
                       NULL                            AS inventory_notes

                FROM crm_pos_sales ps
                         LEFT JOIN dms_customer c ON ps.customer_id = c.id
                         LEFT JOIN crm_pos_register pr ON ps.register_id = pr.id
                         LEFT JOIN crm_pos_outlet po ON pr.outlet_id = po.id
                         JOIN (
                    SELECT psp.sale_id,
                           SUM(psp.subtotal)         sale_amount,
                           SUM(COALESCE(qi.cost, 0)) cost_amount
                    FROM crm_pos_sale_products psp
                             LEFT JOIN qb_items qi ON qi.id = psp.item_id
                    WHERE qi.type = 'trailer'
                    GROUP BY psp.sale_id
                ) sales_unit ON ps.id = sales_unit.sale_id
                         LEFT JOIN (
                    SELECT psp.sale_id,
                           SUM(psp.subtotal) amount
                    FROM crm_pos_sale_products psp
                             JOIN qb_items qi ON qi.id = psp.item_id AND qi.type = 'discount' AND qi.name = 'Inventory Discount'
                    GROUP BY psp.sale_id
                ) inventory_discount ON ps.id = inventory_discount.sale_id
                         LEFT JOIN (
                    SELECT psp.sale_id,
                           SUM(psp.subtotal)         sale_amount,
                           SUM(COALESCE(qi.cost, 0)) cost_amount
                    FROM crm_pos_sale_products psp
                             LEFT JOIN qb_items qi ON qi.id = psp.item_id
                    WHERE qi.type = 'labor'
                    GROUP BY psp.sale_id
                ) sales_labor ON ps.id = sales_labor.sale_id

                WHERE po.dealer_id = :dealerId1
                  $salesPersonClause1
                  AND (ps.total - ps.amount_received) <= 0
                  $dateClause1
                GROUP BY ps.id

                UNION

                /* Sales via BOS/POS/RO Form */
                SELECT i.id                                                                  AS sale_id,
                       i.id                                                                  AS invoice_id,
                       i.doc_num                                                             AS doc_num,
                       i.total                                                               AS total,
                       CASE
                           WHEN i.unit_sale_id IS NOT NULL THEN 'unit_sale_completed'
                           ELSE 'pos' /* @todo must have a clear marker in invoice that it is a pos sale */
                           END                                                               AS sale_type,
                       i.invoice_date                                                           sale_date,
                       IF(i.unit_sale_id IS NOT NULL, us.sales_person_id, i.sales_person_id) AS sales_person_id,
                       c.display_name                                                        AS customer_name,

                       (i.total - payments.paid_amount)                                      AS remaining,

                       iii.cost_overhead                                                     AS cost_overhead,
                       iii.true_total_cost                                                   AS true_total_cost,

                       ii.unit_price                                                         AS sale_amount,
                       COALESCE(qi.cost, 0)                                                  AS cost_amount,

                       ii.unit_price                                                         AS retail_price,
                       (
                           SELECT _ii.unit_price * -1
                           FROM qb_invoice_items _ii
                                    LEFT JOIN qb_items _qi ON _qi.id = _ii.item_id
                           WHERE _ii.referenced_item_id = ii.id
                             AND _qi.type = 'discount'
                             AND _qi.name = 'Inventory Discount'
                       )                                                                     AS retail_discount,

                       iv.stock                                                              AS inventory_stock,
                       iv.manufacturer                                                       AS inventory_make,
                       iv.notes                                                              AS inventory_notes
                FROM qb_invoice_item_inventories iii
                         LEFT JOIN qb_invoice_items ii ON ii.id = iii.invoice_item_id
                         LEFT JOIN qb_invoices i ON i.id = ii.invoice_id
                         LEFT JOIN qb_items qi ON qi.id = ii.item_id
                         LEFT JOIN dms_customer c ON i.customer_id = c.id
                         LEFT JOIN inventory iv ON iv.inventory_id = iii.inventory_id
                         LEFT JOIN (
                    SELECT qb_payment.invoice_id,
                           COALESCE(SUM(qb_payment.amount), 0) paid_amount
                    FROM qb_payment
                    GROUP BY qb_payment.invoice_id
                ) payments ON i.id = payments.invoice_id
                         LEFT JOIN dms_unit_sale us ON us.id = i.unit_sale_id
                         LEFT JOIN dms_repair_order ro ON ro.id = i.repair_order_id
                WHERE i.dealer_id = :dealerId2
                  AND qi.description != 'Trade In Inventory item' /* exclude trade-ins */
                  AND ii.unit_price >= 0 /* exclude trade-ins */
                  $salesPersonClause2
                  AND (i.total - payments.paid_amount) <= 0
                  $dateClause2
            ) sales ON sales.sales_person_id = sp.id
            LEFT JOIN new_dealer_user ndu ON ndu.user_id = sp.user_id
            WHERE sp.deleted_at IS NULL
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
     * @return null|SalesPerson
     */
    public function findNewestSalesPerson(
        int $dealerId,
        int $dealerLocationId,
        string $salesType
    ): ?SalesPerson {
        // Find Newest Salesperson in DB
        $query = SalesPerson::select(SalesPerson::getTableName() . '.*')
                            ->leftJoin(LeadStatus::getTableName(), LeadStatus::getTableName() . '.sales_person_id', '=', SalesPerson::getTableName() . '.id')
                            ->leftJoin(Lead::getTableName(), Lead::getTableName() . '.identifier', '=', LeadStatus::getTableName() . '.tc_lead_identifier')
                            ->leftJoin(NewDealerUser::getTableName(), SalesPerson::getTableName() . '.user_id', '=', NewDealerUser::getTableName() . '.user_id')
                            ->where(Lead::getTableName() . '.dealer_id', $dealerId)
                            ->where(SalesPerson::getTableName() . '.is_' . $salesType, 1)
                            ->where(SalesPerson::getTableName() . '.id', '<>', 0)
                            ->where(SalesPerson::getTableName() . '.id', '<>', '')
                            ->whereNotNull(SalesPerson::getTableName() . '.id')
                            ->whereNotNull(NewDealerUser::getTableName() . '.id')
                            ->orderBy(Lead::getTableName() . '.date_submitted', 'DESC')
                            ->orderBy(Lead::getTableName() . '.identifier', 'DESC');

        // Append Dealer Location
        if(!empty($dealerLocationId)) {
            $query = $query->where(SalesPerson::getTableName() . '.dealer_location_id', $dealerLocationId);
        }

        // Return Sales Person
        return $query->first();
    }

    /**
     * Round Robin to Next Sales Person
     *
     * @param NewDealerUser $dealer
     * @param int $dealerLocationId
     * @param string $salesType
     * @param null|SalesPerson $newestSalesPerson
     * @return null|SalesPerson
     */
    public function roundRobinSalesPerson(
        NewDealerUser $dealer,
        int $dealerLocationId,
        string $salesType,
        ?SalesPerson $newestSalesPerson = null
    ): ?SalesPerson {
        // Kill If Somehow Newest Sales Person Does Not Belong to Current Dealer
        if(!empty($newestSalesPerson->id) && $dealer->user_id !== $newestSalesPerson->user_id) {
            $newestSalesPerson = null;
        }

        // Initialize
        $newestSalesPersonId = $newestSalesPerson->id ?? 0;
        $salesPeople = $this->getSalesPeopleBy($dealer->id, $dealerLocationId, $salesType);

        // Loop Valid Sales People
        $lastId = 0;
        $nextSalesPerson = null;
        if($salesPeople->count() > 1) {
            $lastId = $salesPeople->last()->id;
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
        } elseif($salesPeople->count() === 1) {
            $nextSalesPerson = $salesPeople->first();
        }

        // Return Next Sales Person
        return $nextSalesPerson ?: $newestSalesPerson;
    }

    /**
     * Find Sales People By Dealer ID
     *
     * @param int $dealerId
     * @param null|int $dealerLocationId
     * @param null|string $salesType
     * @return Collection<SalesPerson>
     */
    public function getSalesPeopleBy(
        int $dealerId,
        ?int $dealerLocationId = null,
        ?string $salesType = null
    ): Collection {
        // Get New Sales People By Dealer ID
        $newDealerUser = NewDealerUser::findOrFail($dealerId);
        $query = SalesPerson::select('*')->where('user_id', $newDealerUser->user_id);

        // Check Dealer Location ID on Sales People
        if ($dealerLocationId) {
            $query->where('dealer_location_id', $dealerLocationId);
        }

        // Check Sales Type on Sales People
        if ($salesType) {
            $query->where("is_{$salesType}", 1);
        }

        // Return Collection Results
        return $query->get();
    }

    /**
     * Find Sales Person Type
     *
     * @param string $leadType
     * @return string
     */
    public function findSalesType(string $leadType): string {
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
