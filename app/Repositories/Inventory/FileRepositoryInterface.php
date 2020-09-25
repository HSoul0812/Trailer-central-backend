<?php

namespace App\Repositories\Inventory;

use App\Repositories\Repository;

/**
 * Interface FileRepositoryInterface
 * @package App\Repositories\Inventory
 */
interface FileRepositoryInterface extends Repository
{
    public function getAllByInventoryId(int $inventoryId, $params = []);
}
