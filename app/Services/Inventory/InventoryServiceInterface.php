<?php

namespace App\Services\Inventory;

use Illuminate\Contracts\Pagination\Paginator;

interface InventoryServiceInterface
{
    public function list(array $params): Paginator;
}
