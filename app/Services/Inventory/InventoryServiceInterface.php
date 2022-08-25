<?php

namespace App\Services\Inventory;

use App\DTOs\Inventory\TcEsResponseInventoryList;
use App\DTOs\Inventory\TcApiResponseInventory;
use Illuminate\Support\Collection;

interface InventoryServiceInterface
{
    public function list(array $params): TcEsResponseInventoryList;
    public function show(int $id): TcApiResponseInventory;
    public function attributes(int $entityTypeId): Collection;
}
