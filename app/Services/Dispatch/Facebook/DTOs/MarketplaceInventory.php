<?php

namespace App\Services\Dispatch\Facebook\DTOs;

use App\Services\Dispatch\Facebook\DTOs\InventoryFacebook;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class MarketplaceInventory
 * 
 * @package App\Services\Dispatch\Facebook\DTOs
 */
class MarketplaceInventory
{
    use WithConstructor, WithGetter;

    /**
     * @var string
     */
    private $type;

    /**
     * @var Collection<InventoryFacebook>
     */
    private $inventory;

    /**
     * @var LengthAwarePaginator
     */
    private $paginator;
}