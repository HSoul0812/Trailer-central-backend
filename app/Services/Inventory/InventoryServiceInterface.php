<?php

namespace App\Services\Inventory;

use App\DTOs\Inventory\InventoryListResponse;
use App\DTOs\Inventory\TcApiResponseInventory;

interface InventoryServiceInterface
{
    public function list(array $params): InventoryListResponse;
    public function show(int $id): TcApiResponseInventory;
}
