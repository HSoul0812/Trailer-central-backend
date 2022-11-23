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
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $lastIp;

    /**
     * @var int
     */
    private $count;

    /**
     * @var int
     */
    private $lastCheckin;


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
     * @return null|string
     */
    public function email(): ?string {
        // Find Behaviour for Dealer ID
        $behaviour = Behaviour::byDealerId($this->dealerId);

        // Behaviour Exists?
        if(!empty($behaviour) && $behaviour->email) {
            return $behaviour->email;
        }

        // Return Null
        return null;
    }

    /**
     * Get Behaviour Is Edit If Exists
     * 
     * @return bool
     */
    public function isEdit(): bool {
        // Find Behaviour for Dealer ID
        $behaviour = Behaviour::byDealerId($this->dealerId);

        // Behaviour Exists?
        if(!empty($behaviour)) {
            return $behaviour->edit;
        }

        // Return Null
        return false;
    }
}