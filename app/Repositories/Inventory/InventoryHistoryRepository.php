<?php

declare(strict_types=1);

namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
use App\Exceptions\OperationNotAllowedException;
use App\Models\Inventory\InventoryHistory;
use App\Repositories\Traits\SortTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class InventoryHistoryRepository implements InventoryHistoryRepositoryInterface
{
    use SortTrait;

    private $sortOrders = [
        'created_at' => [
            'field' => 'created_at',
            'direction' => 'DESC'
        ],
        '-created_at' => [
            'field' => 'created_at',
            'direction' => 'ASC'
        ],
        'type' => [
            'field' => 'type',
            'direction' => 'DESC'
        ],
        '-type' => [
            'field' => 'type',
            'direction' => 'ASC'
        ],
        'subtype' => [
            'field' => 'subtype',
            'direction' => 'DESC'
        ],
        '-subtype' => [
            'field' => 'subtype',
            'direction' => 'ASC'
        ],
        'customer_name' => [
            'field' => 'customer_name',
            'direction' => 'DESC'
        ],
        '-customer_name' => [
            'field' => 'customer_name',
            'direction' => 'ASC'
        ]
    ];

    /**
     * @param array $params
     * @param bool $paginated
     * @return Collection|LengthAwarePaginator
     */
    public function getAll($params, bool $paginated = false)
    {
        // sets provided inventory or set empty the result set
        $inventoryId = $params['inventory_id'] ?? 0;

        $query = DB::table(DB::raw(<<<SQL
                (
                    SELECT RO.id,
                           C.id AS customer_id,
                           C.display_name AS customer_name,
                           RO.inventory_id,
                           I.vin,
                           RO.created_at,
                           RO.type COLLATE utf8_unicode_ci AS subtype,
                           SI.problem COLLATE utf8_unicode_ci AS text_1,
                           SI.cause COLLATE utf8_unicode_ci AS text_2,
                           SI.solution COLLATE utf8_unicode_ci AS text_3,
                           SI.amount AS sub_total,
                           RO.total_price AS total,
                           'repair-order' AS type
                    FROM dms_repair_order RO
                    JOIN inventory I ON I.inventory_id = RO.inventory_id
                    LEFT JOIN dms_service_item SI ON SI.repair_order_id = RO.id
                    LEFT JOIN dms_customer C ON C.id = RO.customer_id
                    WHERE RO.inventory_id = ?

                    UNION

                    SELECT US.id,
                           US.buyer_id AS customer_id,
                           C.display_name AS customer_name,
                           US.inventory_id,
                           COALESCE(I.vin COLLATE utf8_unicode_ci, US.inventory_vin) AS vin,
                           US.created_at,
                           NULL AS subtype,
                           US.title AS text_1,
                           US.admin_note AS text_2,
                           NULL AS text_3,
                           US.subtotal AS sub_total,
                           US.total_price AS total,
                           'quote' AS type
                    FROM dms_unit_sale US
                    LEFT JOIN inventory I ON I.inventory_id = US.inventory_id
                    LEFT JOIN dms_customer C ON C.id = US.buyer_id
                    WHERE US.inventory_id = ?

                    UNION

                    SELECT IV.id,
                           IV.customer_id,
                           I.item_primary_id AS inventory_id,
                           C.display_name AS customer_name,
                           IT.vin,
                           IV.invoice_date AS created_at,
                           IV.format AS subtype,
                           IV.doc_num AS text_1,
                           IV.memo AS text_2,
                           SR.receipt_path AS text_3,
                           (II.unit_price * II.qty) AS sub_total,
                           IV.total AS total,
                           'POS' AS type
                    FROM qb_invoice_items II
                    JOIN qb_items I ON I.id = II.item_id AND I.item_primary_id = ? AND I.type = 'trailer'
                    JOIN inventory IT ON IT.inventory_id = I.item_primary_id
                    JOIN qb_invoices IV ON IV.id = II.invoice_id
                    LEFT JOIN crm_pos_sales PO ON PO.invoice_id = IV.id
                    LEFT JOIN dealer_sales_receipt SR ON SR.tb_primary_id = PO.id AND SR.tb_name = 'crm_pos_sales'
                    LEFT JOIN dms_customer C ON C.id = IV.customer_id
                ) AS transactions
SQL
        ));

        $query->setBindings([$inventoryId, $inventoryId, $inventoryId]);
        $query->processor = $this->getProcessor();

        $params['per_page'] = $params['per_page'] ?? 15;

        if (isset($params['customer_id'])) {
            $query->where('customer_id', $params['customer_id']);
        }

        if (isset($params[self::CONDITION_AND_WHERE]) && is_array($params[self::CONDITION_AND_WHERE])) {
            $query->where($params[self::CONDITION_AND_WHERE]);
        }

        if (isset($params['search_term'])) {
            $term = $params['search_term'];

            $query->where(static function ($subQuery) use ($term): void {
                $subQuery->where('customer_name', 'LIKE', "%{$term}%")
                    ->orWhere('type', 'LIKE', "%{$term}%")
                    ->orWhere('subtype', 'LIKE', "%{$term}%")
                    ->orWhere('vin', 'LIKE', "%{$term}%")
                    ->orWhere('text_1', 'LIKE', "%{$term}%")
                    ->orWhere('text_2', 'LIKE', "%{$term}%")
                    ->orWhere('text_3', 'LIKE', "%{$term}%");
            });
        }

        if (isset($params['sort'])) {
            $this->addSortQuery($query, $params['sort']);
        }

        return $paginated ? $query->paginate($params['per_page'])->appends($params) : $query->get();
    }

    /**
     * @param $params
     * @return InventoryHistory
     *
     * @throws OperationNotAllowedException
     */
    public function create($params): InventoryHistory
    {
        throw new OperationNotAllowedException();
    }

    /**
     * @param array $params
     * @return InventoryHistory
     *
     * @throws OperationNotAllowedException
     */
    public function update($params): InventoryHistory
    {
        throw new OperationNotAllowedException();
    }

    /**
     * @param array $params
     * @return InventoryHistory
     */
    public function get($params): InventoryHistory
    {
        throw new NotImplementedException('Not implemented yet.');
    }

    /**
     * @param array $params
     * @return boolean
     *
     * @throws OperationNotAllowedException
     */
    public function delete($params): bool
    {
        throw new OperationNotAllowedException();
    }

    protected function getSortOrders(): array
    {
        return $this->sortOrders;
    }

    /**
     * Provides a processor for the query builder
     *
     * @return Processor
     */
    private function getProcessor(): Processor
    {
        return new class extends Processor {
            /**
             * Query model hydration
             *
             * @param Builder $query
             * @param $results
             * @return array
             */
            public function processSelect(Builder $query, $results): array
            {
                return collect($results)->map(static function (stdClass $record) {
                    return isset($record->id) ? InventoryHistory::from($record) : $record;
                })->toArray();
            }
        };
    }
}
