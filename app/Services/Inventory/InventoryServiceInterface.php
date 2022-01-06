<?php

declare(strict_types=1);

namespace App\Services\Inventory;

interface InventoryServiceInterface
{
    /**
     * @param int $id the id of the inventory
     */
    public function show(int $id);
}
