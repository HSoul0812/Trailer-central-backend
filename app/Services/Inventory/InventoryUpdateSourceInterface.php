<?php

namespace App\Services\Inventory;

interface InventoryUpdateSourceInterface
{
    /**
     * @return bool
     */
    public function integrations(): bool;
}
