<?php

namespace App\Service\Marketing\Craigslist\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class Client
 * 
 * @package App\Service\Marketing\Craigslist\DTOs
 */
class Client
{
    use WithConstructor, WithGetter;

    /**
     * @var int
     */
    private $dealerId;

    /**
     * @var int
     */
    private $slotId;

    /**
     * @var string
     */
    private $uuid;

    /**
     * @var int
     */
    private $count;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $lastIp;

    /**
     * @var int
     */
    private $lastCheckin;

    /**
     * @var string
     */
    private $label;


    /**
     * Get Elapsed Minutes
     * 
     * @return int
     */
    public function elapsed(): int {
        // Return Elapsed Seconds
        $elapsed = time() - $this->lastCheckin;

        // Convert to Minutes
        return floor($elapsed / 60);
    }

    /**
     * Get Behaviour Email If Exists
     * 
     * @return array
     */
    
}