<?php

namespace App\Repositories\Inventory;

use App\Repositories\Repository;

/**
 * Interface InventoryImageInterface
 * @package App\Repositories\Inventory
 */
interface ImageRepositoryInterface extends Repository
{
    public function getAllByInventoryId(int $inventoryId, $params = []);

    public function scheduleObjectToBeDroppedByURL(string $url): void;
}
