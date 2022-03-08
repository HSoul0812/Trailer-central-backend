<?php

namespace App\Services\Dispatch\Facebook\DTOs;

use App\Services\Dispatch\Facebook\DTOs\DealerFacebook;
use App\Services\Dispatch\Facebook\DTOs\DealerTunnel;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class MarketplaceStatus
 * 
 * @package App\Services\Dispatch\Facebook\DTOs
 */
class MarketplaceStatus
{
    use WithConstructor, WithGetter;

    /**
     * @const Default Action
     */
    const DEFAULT_ACTION = 'start-script';


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
     * @var Collection<DealerFacebook>
     */
    private $dealers;

    /**
     * @var Collection<DealerTunnel>
     */
    private $tunnels;


    /**
     * Get Default Action
     * 
     * @return array
     */
    public function getAction() {
        return config('marketing.fb.settings.action', self::DEFAULT_ACTION);
    }

    /**
     * Get All URL's
     * 
     * @return array
     */
    public function getAllUrls() {
        return config('marketing.fb.settings.urls', []);
    }

    /**
     * Get All Selectors
     * 
     * @return array
     */
    public function getAllSelectors() {
        return config('marketing.fb.selectors', []);
    }
}