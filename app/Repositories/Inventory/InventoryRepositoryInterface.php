<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\Inventory;
use App\Repositories\Repository;

interface InventoryRepositoryInterface extends Repository
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

    public function beginTransaction(): void;

    public function commitTransaction(): void;

    public function rollbackTransaction(): void;

    /**
     * @return int number of touched records
     */
    public function moveLocationId(int $from, int $to): int;
}
