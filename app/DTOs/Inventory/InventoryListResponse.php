<?php

namespace App\DTOs\Inventory;

use Illuminate\Pagination\LengthAwarePaginator;

class InventoryListResponse
{
    public array $aggregations;
    public LengthAwarePaginator $inventories;
}
