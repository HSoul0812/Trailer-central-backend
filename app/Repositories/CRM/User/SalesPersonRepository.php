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
     * Delete Sales Person
     * 
     * @param array $params
     * @return bool true if deleted, false if doesn't exist
     */
    public function delete($params) {
        return SalesPerson::findOrFail($params['id'])->delete();
    }

    /**
     * Update Sales Person
     *
     * @param array $params
     * @return SalesPerson
     */
    public function update($params) {
        $salesPerson = SalesPerson::withTrashed()->findOrFail($params['id']);

        DB::transaction(function() use (&$salesPerson, $params) {
            // Restore if Soft Deleted
            $salesPerson->restore();

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

    public function getEmailSignature(int $salesPersonId)
    {
        return $this->get(['sales_person_id' => $salesPersonId])->signature;
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

        // Implement With
        if (!empty($params['with']) && is_array($params['with'])) {
            foreach ($params['with'] as $with) {
                $query = $query->with($with);
            }
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
     * @return Collection<SalesPerson>
     */
    public function getAllImap(int $userId): Collection {
        return SalesPerson::select(SalesPerson::getTableName().'.*')
                          ->leftJoin(AccessToken::getTableName(), function($join) {
            $join->on(AccessToken::getTableName().'.relation_id', '=', SalesPerson::getTableName().'.id')
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
        })->where(function($query) {
            $query->whereNull('imap_failed')
                  ->orWhere('imap_failed', 0);
        })->groupBy(SalesPerson::getTableName().'.id')->get();
    }

    /**
     * Get By Email
     *
     * @param int $userId
     * @param string $email
     * @return null|SalesPerson
     */
    public function getByEmail(int $userId, string $email): ?SalesPerson {
        // Get SalesPerson By User ID and Email
        return SalesPerson::withTrashed()->where('user_id', $userId)->where('email', $email)->first();
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
        $dealerId = $params['dealer_id'];

        $dbParams = [
            'posSalesDealerId' => $params['dealer_id'],
            'otherSalesDealerId' => $params['dealer_id'],
            'refundSalesDealerId' => $params['dealer_id'],
            'specialRefundSalesDealerId' => $params['dealer_id'],
        ];

        $posSalesDateClause = ''; // no date filters
        $otherSalesDateClause = ''; // no date filters
        $refundSalesDateClause = ''; // no date filters
        $specialRefundSalesDateClause = ''; // no date filters

        $posSalesSalesPersonClause = ''; // no date filters
        $otherSalesSalesPersonClause = ''; // no date filters
        $refundSalesSalesPersonClause = ''; // no date filters
        $specialRefundSalesSalesPersonClause = ''; // no date filters

        if (!empty($params['salesperson'])) {
            $posSalesSalesPersonClause = 'AND ps.sales_person_id = :posSalesSalesPerson';
            $otherSalesSalesPersonClause = 'AND IF(i.unit_sale_id IS NOT NULL, us.sales_person_id, i.sales_person_id) = :otherSalesSalesPerson';
            $refundSalesSalesPersonClause = 'AND dms_unit_sale.sales_person_id = :refundSalesSalesPerson';
            $specialRefundSalesSalesPersonClause = 'AND crm_sales_person.id = :specialRefundSalesSalesPerson';

            $dbParams['posSalesSalesPerson'] = $params['salesperson'];
            $dbParams['otherSalesSalesPerson'] = $params['salesperson'];
            $dbParams['refundSalesSalesPerson'] = $params['salesperson'];
            $dbParams['specialRefundSalesSalesPerson'] = $params['salesperson'];
        }
        if (!empty($params['from_date']) && !empty($params['to_date'])) {
            $posSalesDateClause = 'AND DATE(ps.created_at) BETWEEN :posSalesFrom AND :posSalesTo';
            $otherSalesDateClause = 'AND DATE(i.invoice_date) BETWEEN :otherSalesFrom AND :otherSalesTo';
            $refundSalesDateClause = 'AND DATE(dealer_refunds.created_at) BETWEEN :refundSalesFrom AND :refundSalesTo';
            $specialRefundSalesDateClause = 'AND DATE(dealer_refunds.created_at) BETWEEN :specialRefundSalesFrom AND :specialRefundSalesTo';

            $dbParams['posSalesFrom'] = $params['from_date'];
            $dbParams['otherSalesFrom'] = $params['from_date'];
            $dbParams['refundSalesFrom'] = $params['from_date'];
            $dbParams['specialRefundSalesFrom'] = $params['from_date'];
            $dbParams['posSalesTo'] =  $params['to_date'];
            $dbParams['otherSalesTo'] = $params['to_date'];
            $dbParams['refundSalesTo'] = $params['to_date'];
            $dbParams['specialRefundSalesTo'] = $params['to_date'];
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
                       NULL                            AS inventory_notes,

                       NULL                            AS unit_sale_id
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

                WHERE po.dealer_id = :posSalesDealerId
                  $posSalesSalesPersonClause
                  AND (ps.total - ps.amount_received) <= 0
                  $posSalesDateClause
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

                       (
                        SELECT cost_overhead
                        FROM (
                            SELECT
                                cost_overhead_inventory.inventory_id,
                                @total_of_cost := (
                                    coalesce(nullif(trim(cost_overhead_inventory.cost_of_shipping), ''), 0) +
                                    coalesce(nullif(trim(cost_overhead_inventory.cost_of_prep), ''), 0) +
                                    coalesce(nullif(trim(cost_overhead_inventory.cost_of_unit), ''), 0) +
                                    coalesce((
                                      SELECT
                                        sum(dms_repair_order.total_price) AS total_price
                                      FROM
                                        dms_repair_order
                                      WHERE
                                        dms_repair_order.inventory_id = cost_overhead_inventory.inventory_id
                                        AND dms_repair_order.type = 'internal'
                                        AND dms_repair_order.dealer_id = $dealerId
                                      GROUP BY
                                        dms_repair_order.inventory_id
                                    ), 0)
                                ) AS total_of_cost,
                                @pac_total_amount := coalesce(
                                  if(strcmp(cost_overhead_inventory.pac_type, 'percent') = 0, (@total_of_cost * cost_overhead_inventory.pac_amount) / 100, cost_overhead_inventory.pac_amount),
                                  0
                                ) AS pac_total_amount,
                                (@total_of_cost + @pac_total_amount) AS cost_overhead
                            FROM
                                inventory AS cost_overhead_inventory
                            WHERE
                                cost_overhead_inventory.dealer_id = $dealerId
                        ) as cost_overhead_result
                        where cost_overhead_result.inventory_id = iv.inventory_id
                      ) AS cost_overhead,
                    
                      (
                       SELECT 
                        (
                          coalesce(
                            true_total_cost_inventory.true_cost,
                            0
                          ) + coalesce(
                            true_total_cost_inventory.cost_of_shipping,
                            0
                          ) + coalesce(
                            true_total_cost_inventory.cost_of_prep,
                            0
                          ) + coalesce(
                            (
                              SELECT 
                                sum(dms_repair_order.total_price) AS total_price 
                              FROM 
                                dms_repair_order 
                              WHERE 
                                dms_repair_order.inventory_id = true_total_cost_inventory.inventory_id 
                                AND dms_repair_order.type = 'internal' 
                              GROUP BY 
                                dms_repair_order.inventory_id
                            ),
                            0
                          )
                        ) AS true_total_cost 
                       FROM 
                        inventory AS true_total_cost_inventory 
                       WHERE 
                        true_total_cost_inventory.inventory_id = iv.inventory_id
                       ) AS true_total_cost,

                       ii.unit_price                                                         AS unit_sale_amount,
                       COALESCE(qi.cost, 0)                                                  AS unit_cost_amount,

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
                       iv.notes                                                              AS inventory_notes,
                       i.unit_sale_id                                                        AS unit_sale_id
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
                WHERE i.dealer_id = :otherSalesDealerId
                  AND qi.description != 'Trade In Inventory item' /* exclude trade-ins */
                  AND ii.unit_price >= 0 /* exclude trade-ins */
                  $otherSalesSalesPersonClause
                  $otherSalesDateClause
                
                UNION
                
                /* Normal Refund */
                SELECT 
                    dealer_refunds.id AS sale_id,
                    qb_payment.invoice_id,
                    qb_invoices.doc_num,
                    qb_invoices.total,
                    'refund' AS sale_type,
                    qb_invoices.invoice_date AS sale_date,
                    dms_unit_sale.sales_person_id,
                    dms_customer.display_name AS customer_name,
                    (
                      qb_invoices.total - payments.paid_amount
                    ) AS remaining,
                    qb_invoice_item_inventories.cost_overhead * -1 as cost_overhead,
                    qb_invoice_item_inventories.true_total_cost,
                    qb_invoice_items.unit_price AS unit_sale_amount,
                    COALESCE(qb_items.cost,0) AS unit_cost_amount,
                    qb_invoice_items.unit_price * -1 AS retail_price,
                    (
                      SELECT 
                        refund_qb_invoice_items.unit_price * -1 
                      FROM 
                        qb_invoice_items refund_qb_invoice_items 
                        JOIN qb_items refund_qb_items ON refund_qb_items.id = refund_qb_invoice_items.item_id 
                      WHERE 
                        refund_qb_invoice_items.referenced_item_id = qb_invoice_items.id 
                        AND refund_qb_items.type = 'discount' 
                        AND refund_qb_items.name = 'Inventory Discount'
                    ) AS retail_discount,
                    inventory.stock AS inventory_stock,
                    inventory.manufacturer AS inventory_make,
                    inventory.notes AS inventory_notes,
                    qb_invoices.unit_sale_id 
                FROM 
                    dealer_refunds 
                    JOIN qb_payment ON qb_payment.id = dealer_refunds.tb_primary_id 
                    JOIN qb_invoices ON qb_payment.invoice_id = qb_invoices.id 
                    JOIN dms_unit_sale ON dms_unit_sale.id = qb_invoices.unit_sale_id 
                    JOIN inventory ON inventory.inventory_id = dms_unit_sale.inventory_id 
                    JOIN crm_sales_person ON crm_sales_person.id = dms_unit_sale.sales_person_id 
                    JOIN dms_customer ON dms_customer.id = qb_invoices.customer_id 
                    JOIN qb_invoice_items ON qb_invoice_items.invoice_id = qb_invoices.id 
                    JOIN qb_invoice_item_inventories ON qb_invoice_item_inventories.invoice_item_id = qb_invoice_items.id 
                    JOIN qb_items ON qb_items.id = qb_invoice_items.item_id 
                    JOIN (
                      SELECT 
                        qb_payment.invoice_id,
                        COALESCE(
                          SUM(qb_payment.amount),
                          0
                        ) paid_amount 
                      FROM 
                        qb_payment 
                      GROUP BY 
                        qb_payment.invoice_id
                    ) payments ON qb_invoices.id = payments.invoice_id
                WHERE
                    dealer_refunds.dealer_id = :refundSalesDealerId
                    AND dealer_refunds.tb_name = 'qb_payment' 
                    AND qb_invoices.unit_sale_id IS NOT null
                    $refundSalesSalesPersonClause
                    $refundSalesDateClause
                
                UNION
                
                /* Special refund (where trade-in value is higher than unit value) */
                SELECT
                    dealer_refunds.id AS sale_id,
                    '' AS invoice_id,
                    '' AS doc_num,
                    dealer_refunds.amount AS total,
                    'refund' AS sale_type,
                    dealer_refunds.created_at AS sale_date,
                    crm_sales_person.id AS sales_person_id,
                    dms_customer.display_name AS customer_name,
                    0 AS remaining,
                    (
                        SELECT cost_overhead
                        FROM (
                            SELECT
                                cost_overhead_inventory.inventory_id,
                                @total_of_cost := (
                                    coalesce(nullif(trim(cost_overhead_inventory.cost_of_shipping), ''), 0) +
                                    coalesce(nullif(trim(cost_overhead_inventory.cost_of_prep), ''), 0) +
                                    coalesce(nullif(trim(cost_overhead_inventory.cost_of_unit), ''), 0) +
                                    coalesce((
                                      SELECT
                                        sum(dms_repair_order.total_price) AS total_price
                                      FROM
                                        dms_repair_order
                                      WHERE
                                        dms_repair_order.inventory_id = cost_overhead_inventory.inventory_id
                                        AND dms_repair_order.type = 'internal'
                                        AND dms_repair_order.dealer_id = $dealerId
                                      GROUP BY
                                        dms_repair_order.inventory_id
                                    ), 0)
                                ) AS total_of_cost,
                                @pac_total_amount := coalesce(
                                  if(strcmp(cost_overhead_inventory.pac_type, 'percent') = 0, (@total_of_cost * cost_overhead_inventory.pac_amount) / 100, cost_overhead_inventory.pac_amount),
                                  0
                                ) AS pac_total_amount,
                                (@total_of_cost + @pac_total_amount) AS cost_overhead
                            FROM
                                inventory AS cost_overhead_inventory
                            WHERE
                                cost_overhead_inventory.dealer_id = $dealerId
                        ) as cost_overhead_result
                        where cost_overhead_result.inventory_id = inventory.inventory_id
                    ) AS cost_overhead,
                    (
                      SELECT 
                        (
                          coalesce(
                            true_total_cost_inventory.true_cost,
                            0
                          ) + coalesce(
                            true_total_cost_inventory.cost_of_shipping,
                            0
                          ) + coalesce(
                            true_total_cost_inventory.cost_of_prep,
                            0
                          ) + coalesce(
                            (
                              SELECT 
                                sum(dms_repair_order.total_price) AS total_price 
                              FROM 
                                dms_repair_order 
                              WHERE 
                                dms_repair_order.inventory_id = true_total_cost_inventory.inventory_id 
                                AND dms_repair_order.type = 'internal' 
                              GROUP BY 
                                dms_repair_order.inventory_id
                            ),
                            0
                          )
                        ) AS true_total_cost 
                      FROM 
                        inventory AS true_total_cost_inventory 
                      WHERE 
                        true_total_cost_inventory.inventory_id = inventory.inventory_id
                    ) AS true_total_cost,
                    qb_items.unit_price AS unit_sale_amount,
                    qb_items.cost AS unit_cost_amount,
                    qb_items.unit_price AS retail_price,
                    (
                      SELECT 
                        sales_refund_qb_items.unit_price * -1 
                      FROM 
                        dealer_refunds_items sales_refund_refunds_items
                        JOIN qb_items sales_refund_qb_items ON sales_refund_qb_items.id = sales_refund_refunds_items.item_id 
                      WHERE
                        sales_refund_refunds_items.dealer_refunds_id = dealer_refunds.id 
                        AND sales_refund_qb_items.type = 'discount' 
                        AND sales_refund_qb_items.name = 'Inventory Discount'
                    ) AS retail_discount,
                    inventory.stock AS inventory_stock,
                    inventory.manufacturer AS inventory_make,
                    inventory.notes AS inventory_notes,
                    dms_unit_sale.id AS unit_sale_id
                FROM
                    dealer_refunds 
                    JOIN dms_unit_sale ON dms_unit_sale.id = dealer_refunds.tb_primary_id 
                    JOIN crm_sales_person ON crm_sales_person.id = dms_unit_sale.sales_person_id 
                    JOIN dms_customer ON dms_customer.id = dms_unit_sale.buyer_id 
                    JOIN inventory ON inventory.inventory_id = dms_unit_sale.inventory_id
                    JOIN dealer_refunds_items ON dealer_refunds_items.dealer_refunds_id = dealer_refunds.id
                    JOIN qb_items ON qb_items.id = dealer_refunds_items.item_id and qb_items.type = 'trailer'
                WHERE   
                    dealer_refunds.dealer_id = :specialRefundSalesDealerId 
                    AND dealer_refunds.tb_name = 'dms_unit_sale' 
                    $specialRefundSalesDateClause
                    $specialRefundSalesSalesPersonClause

            ) sales ON sales.sales_person_id = sp.id
            LEFT JOIN new_dealer_user ndu ON ndu.user_id = sp.user_id
            WHERE sp.deleted_at IS NULL
            ORDER BY sales.sale_date DESC
SQL;

        $result = DB::select($sql, $dbParams);

        $unitSaleIds = array_filter(array_column($result, 'unit_sale_id'));

        $paidAmounts = [];

        if (!empty($unitSaleIds)) {
            $unitSaleIdsQuestionMarks = implode(',', array_fill(0, count($unitSaleIds), '?'));

            // For down payment invoices
            $sql2 = <<<SQL
             SELECT qb_invoices.unit_sale_id,
                  COALESCE(SUM(qb_payment.amount), 0) paid_amount
             FROM qb_payment
             JOIN qb_invoices ON qb_invoices.id = qb_payment.invoice_id
             WHERE qb_invoices.unit_sale_id IN ($unitSaleIdsQuestionMarks)
             GROUP BY qb_invoices.unit_sale_id
SQL;

            foreach (DB::select($sql2, $unitSaleIds) as $paidAmount) {
                $paidAmounts[$paidAmount->unit_sale_id] = $paidAmount;
            }
        }

        foreach ($result as $item) {
            if (isset($paidAmounts[$item->unit_sale_id]) && $item->remaining > 0) {
                $paidAmount = $paidAmounts[$item->unit_sale_id]->paid_amount;

                $item->remaining = number_format((float)($item->total - $paidAmount), 2, '.', '');
            }
        }

        $result = array_filter($result, function ($item) {
            return $item->remaining <= 0;
        });

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
