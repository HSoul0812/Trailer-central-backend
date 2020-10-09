<?php

namespace App\Repositories\Inventory;

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

    public function getFloorplannedInventory($params);

    public function beginTransaction(): void;

    public function commitTransaction(): void;

    public function rollbackTransaction(): void;
}
