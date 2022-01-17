<?php

namespace App\DTOs\Inventory;

use Illuminate\Pagination\LengthAwarePaginator;

class TcEsResponseInventoryList
{
    public array $aggregations;
    public LengthAwarePaginator $inventories;
}
