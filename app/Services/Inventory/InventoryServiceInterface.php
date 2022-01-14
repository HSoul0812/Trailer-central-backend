<?php

namespace App\Services\Inventory;

use App\DTOs\Inventory\InventoryListResponse;

interface InventoryServiceInterface
{
    public function list(array $params): InventoryListResponse;
}
