<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\Inventory;
use App\Repositories\Repository;
use App\Repositories\TransactionalRepository;

interface InventoryRepositoryInterface extends Repository, TransactionalRepository
{
    const DEFAULT_GET_PARAMS = [
        self::CONDITION_AND_WHERE => [
            ['active', '=', 1],
            ['is_archived', '<>', 1]
        ]
    ];

    public function getAll($params, bool $withDefault = true, bool $paginated = false);

    public function exists(array $params);

    public function getAllWithHavingCount($params, bool $withDefault = true);

    public function getFloorplannedInventory($params);

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
}
