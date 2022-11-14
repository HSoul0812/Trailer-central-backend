<?php

namespace App\Services\Inventory;

use App\DTOs\Inventory\TcEsResponseInventoryList;

interface InventorySDKServiceInterface
{
    public function list(array $params): TcEsResponseInventoryList;
}
