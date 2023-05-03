<?php

namespace App\DTOs\Inventory;

use Illuminate\Pagination\LengthAwarePaginator;

class TcEsResponseInventoryList
{
    public array $aggregations;

    public array $limits;

    public LengthAwarePaginator $inventories;

    public array $esQuery;

    public array $sdkPayload;
}
