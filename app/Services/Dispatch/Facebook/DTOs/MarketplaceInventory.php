<?php

namespace App\Services\Dispatch\Facebook\DTOs;

use App\Services\Dispatch\Facebook\DTOs\InventoryFacebook;
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
     * @var Collection<InventoryFacebook>
     */
    private $create;

    /**
     * @var Collection<InventoryFacebook>
     */
    private $update;

    /**
     * @var Collection<InventoryFacebook>
     */
    private $delete;
}