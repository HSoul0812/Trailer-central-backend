<?php

namespace App\Services\Dispatch\Craigslist\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class DealerCraigslist
 * 
 * @package App\Services\Dispatch\Craigslist\DTOs
 */
class DealerCraigslist
{
    use WithConstructor, WithGetter;


    /**
     * @const Inventory Methods
     */
    const DEALER_TYPES = [
        'posted',    // dealer has posted anything to CL
        'profiles',  // has any non-deleted profiles
        'scheduled', // has scheduled anything in the scheduler
        'upcoming',  // has anything scheduled for the future
        'now'        // has anything scheduled for right now
    ];


    /**
     * @var string - in self::DEALER_TYPES
     */
    private $type;

    /**
     * @var int
     */
    private $dealerId;

    /**
     * @var int
     */
    private $slots;

    /**
     * @var int
     */
    private $chromeMode;

    /**
     * @var string
     */
    private $since;

    /**
     * @var string
     */
    private $next;

    /**
     * @var string
     */
    private $dealerName;

    /**
     * @var string
     */
    private $dealerEmail;

    /**
     * @var string
     */
    private $dealerType;

    /**
     * @var string
     */
    private $dealerState;


    /**
     * Get Proxy Settings
     * 
     * @return array
     */
    public function getProxyConfig(): array {
        return config('marketing.cl.settings.proxy', []);
    }

    /**
     * Get Cookie Settings
     * 
     * @return array
     */
    public function getCookieConfig(): array {
        return config('marketing.cl.settings.cookie', []);
    }

    /**
     * Get All URL's
     * 
     * @return array
     */
    public function getAllUrls(): array {
        return config('marketing.cl.settings.urls', []);
    }

    /**
     * Get All Selectors
     * 
     * @return array
     */
    public function getAllSelectors(): array {
        return config('marketing.cl.selectors', []);
    }
}