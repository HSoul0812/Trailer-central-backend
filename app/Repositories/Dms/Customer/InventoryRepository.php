<?php

declare(strict_types=1);

namespace App\Repositories\Dms\Customer;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dms\Customer\CustomerInventory;
use App\Models\Inventory\Inventory;
use App\Repositories\Traits\SortTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class InventoryRepository implements InventoryRepositoryInterface
{
    use SortTrait;

    private $sortOrders = [
        'title' => [
            'field' => 'title',
            'direction' => 'DESC'
        ],
        '-title' => [
            'field' => 'title',
            'direction' => 'ASC'
        ],
        'manufacturer' => [
            'field' => 'manufacturer',
            'direction' => 'DESC'
        ],
        '-manufacturer' => [
            'field' => 'manufacturer',
            'direction' => 'ASC'
        ],
        'vin' => [
            'field' => 'vin',
            'direction' => 'DESC'
        ],
        '-vin' => [
            'field' => 'vin',
            'direction' => 'ASC'
        ],
        'status' => [
            'field' => 'status',
            'direction' => 'DESC'
        ],
        '-status' => [
            'field' => 'status',
            'direction' => 'ASC'
        ],
    ];

    /**
     * @param array $params  array of values
     * @return CustomerInventory
     */
    public function create($params): CustomerInventory
    {
        return CustomerInventory::create($params);
    }

    /**
     * @param array<string> $uuids
     * @return bool
     */
    public function bulkDestroy(array $uuids): bool
    {
        return (bool)CustomerInventory::whereIn('uuid', $uuids)->delete();
    }

    /**
     * @param array $params
     * @param bool $paginated
     * @return Collection|LengthAwarePaginator
     */
    public function getAll($params, bool $paginated = false)
    {
        $customerInventoryTableName = CustomerInventory::getTableName();
        $inventoryTableName = Inventory::getTableName();

        $query = Inventory::select("$inventoryTableName.*");

        if (isset($params['dealer_id'])) {
            $query->where("$inventoryTableName.dealer_id", $params['dealer_id']);
        }

        if (isset($params['customer_id'])) {
            if (isset($params['customer_condition']) && $params['customer_condition'] === self::TENANCY_CONDITION['does_not_have']) {
                $query->where(static function (EloquentBuilder $query) use (
                    $params,
                    $customerInventoryTableName
                ): void {
                    $query->whereDoesntHave(
                        'customerInventories',
                        static function (EloquentBuilder $query) use ($params, $customerInventoryTableName): void {
                            $query->where("$customerInventoryTableName.customer_id", $params['customer_id']);
                        });
                });
            } else {
                $query->join(
                    $customerInventoryTableName,
                    static function (JoinClause $join) use (
                        $inventoryTableName,
                        $customerInventoryTableName,
                        $params
                    ): void {
                        $join->on("$customerInventoryTableName.inventory_id", '=', "$inventoryTableName.inventory_id")
                            ->where("$customerInventoryTableName.customer_id", $params['customer_id']);
                    })->addSelect("$customerInventoryTableName.uuid as customer_inventory_id");
            }
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (isset($params[self::CONDITION_AND_WHERE]) && is_array($params[self::CONDITION_AND_WHERE])) {
            $query->where($params[self::CONDITION_AND_WHERE]);
        }

        if (isset($params[self::CONDITION_AND_WHERE_IN]) && is_array($params[self::CONDITION_AND_WHERE_IN])) {
            foreach ($params[self::CONDITION_AND_WHERE_IN] as $field => $values) {
                $query->whereIn($field, $values);
            }
        }

        if (isset($params['search_term'])) {
            $query->where(static function (EloquentBuilder $query) use ($params
            ) {
                $query->where('stock', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('title', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('description', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('vin', 'LIKE', '%' . $params['search_term'] . '%');
            });
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        if ($paginated) {
            return $query->paginate($params['per_page'])->appends($params);
        }

        return $query->get();
    }

    /**
     * @param array $params
     * @return bool|void
     */
    public function update($params): bool
    {
        throw new NotImplementedException('Not implemented yet.');
    }

    /**
     * @param array $params
     * @return CustomerInventory
     */
    public function get($params)
    {
        $query = CustomerInventory::select('*');
        if (isset($params['customer_id'])) {
            $query->where('customer_id', $params['customer_id']);
        }

        if (isset($params['inventory_id'])) {
            $query->where('inventory_id', $params['inventory_id']);
        };
        return $query->firstOrFail();
    }

    /**
     * @param $params
     * @return boolean
     */
    public function delete($params): bool
    {
        throw new NotImplementedException('Not implemented yet.');
    }

    protected function getSortOrders(): array
    {
        return $this->sortOrders;
    }

    /**
     * @param int $customerId
     *
     * @return Collection
     */
    public function getTitles(int $customerId)
    {
        $customerInventoryTable = CustomerInventory::getTableName();
        $inventoryTable = Inventory::getTableName();

        $query = Inventory::select(["$inventoryTable.inventory_id", 'title', 'vin']);

        $query->join(
            $customerInventoryTable,
            static function (JoinClause $join) use ($inventoryTable, $customerInventoryTable, $customerId): void {
                $join->on("$customerInventoryTable.inventory_id", '=', "$inventoryTable.inventory_id")
                ->where("$customerInventoryTable.customer_id", $customerId);
            }
        );

        return $query->get();
    }
}
