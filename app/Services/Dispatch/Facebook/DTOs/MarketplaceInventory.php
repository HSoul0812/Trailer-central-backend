<?php

namespace App\Services\Dispatch\Facebook\DTOs;

use App\Services\Dispatch\Facebook\DTOs\InventoryFacebook;
use App\Services\Dispatch\Facebook\DTOs\MarketplaceStatus;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class MarketplaceInventory
 * 
 * @package App\Services\Dispatch\Facebook\DTOs
 */
class MarketplaceInventory
{
    use WithConstructor, WithGetter;

    /**
     * @const Response Default
     */
    const METHOD_DEFAULT = MarketplaceStatus::METHOD_MISSING;


    /**
     * @var string
     */
    private $type = self::METHOD_DEFAULT;

    /**
     * @var Collection<InventoryFacebook>
     */
    private $inventory;

    /**
     * @var LengthAwarePaginator
     */
    private $paginator;
}