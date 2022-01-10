<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\DTOs\Inventory\TcApiResponseInventory;

interface InventoryServiceInterface
{
    /**
     * @param int $id the id of the inventory
     */
    public function show(int $id): TcApiResponseInventory;
}
