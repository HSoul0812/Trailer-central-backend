<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\Inventory;
use App\Repositories\Repository;
use App\Repositories\TransactionalRepository;
use Illuminate\Support\LazyCollection;

interface InventoryRepositoryInterface extends Repository, TransactionalRepository
{
    const DEFAULT_GET_PARAMS = [
        self::CONDITION_AND_WHERE => [
            ['active', '=', 1],
            ['is_archived', '<>', 1]
        ]
    ];

    public function getAll($params, bool $withDefault = true, bool $paginated = false, $select = []);

    /**
     * Gets the query cursor to avoid memory leaks
     *
     * @param array $params
     * @return LazyCollection
     */
    public function getAllAsCursor(array $params): LazyCollection;

    public function exists(array $params);

    public function getAllWithHavingCount($params, bool $withDefault = true);

    public function getFloorplannedInventory($params);

    /**
     * Gets the query cursor to avoid memory leaks
     *
     * @param array $params
     * @return LazyCollection
     */
    public function getFloorplannedInventoryAsCursor(array $params): LazyCollection;

    public function getPopularInventory(int $dealer_id);

    public function update($params, array $options = []): Inventory;

    /**
     * @return int number of touched records
     */
    public function moveLocationId(int $from, int $to): int;

    /**
     * Returns data about an inventory item and increments its times viewed
     * counter
     *
     * @param array $params
     * @return Inventory
     */
    public function getAndIncrementTimesViewed(array $params): Inventory;

    /**
     * Archived Inventory units from specific dealer id
     *
     * @param int $dealerId
     * @param array $inventoryParams
     * @return mixed
     */
    public function archiveInventory(int $dealerId, array $inventoryParams);

    /**
     * Find the inventory by stock
     *
     * @param int $dealerId
     * @param string $stock
     * @return Inventory|null
     */
    public function findByStock(int $dealerId, string $stock): ?Inventory;
}
