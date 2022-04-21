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
     * @const Default Interval
     */
    const DEFAULT_INTERVAL = 60 * 60;


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
     * @return string
     */
    public function getAction(): string {
        return config('marketing.fb.settings.action', self::DEFAULT_ACTION);
    }

    /**
     * Get Default Interval
     * 
     * @return int
     */
    public function getInterval(): int {
        return (int) config('marketing.fb.settings.interval', self::DEFAULT_INTERVAL);
    }

    /**
     * Get Proxy Settings
     * 
     * @return array
     */
    public function getProxyConfig(): array {
        return config('marketing.fb.settings.proxy', []);
    }

    /**
     * Get Cookie Settings
     * 
     * @return array
     */
    public function getCookieConfig(): array {
        return config('marketing.fb.settings.cookie', []);
    }

    /**
     * Get All URL's
     * 
     * @return array
     */
    public function getAllUrls(): array {
        return config('marketing.fb.settings.urls', []);
    }

    /**
     * Get All Selectors
     * 
     * @return array
     */
    public function getAllSelectors(): array {
        return config('marketing.fb.selectors', []);
    }
}