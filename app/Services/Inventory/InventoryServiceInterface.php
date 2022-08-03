<?php

namespace App\Services\Inventory;

use App\DTOs\Inventory\TcEsResponseInventoryList;
use App\DTOs\Inventory\TcApiResponseInventory;

interface InventoryServiceInterface
{
    public function list(array $params): TcEsResponseInventoryList;
    public function show(int $id): TcApiResponseInventory;
    public function attributes(int $entityTypeId): array;
}
