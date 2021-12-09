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
     * @const Inventory Methods
     */
    const INVENTORY_METHODS = [
        'missing' => 'getAllMissing',
        'updates' => 'getAllUpdates',
        'sold'    => 'getAllSold'
    ];

    /**
     * @const Missing Method
     */
    const METHOD_MISSING = 'missing';


    /**
     * @var Collection<InventoryFacebook>
     */
    private $missing;

    /**
     * @var Collection<InventoryFacebook>
     */
    private $updates;

    /**
     * @var Collection<InventoryFacebook>
     */
    private $sold;
}