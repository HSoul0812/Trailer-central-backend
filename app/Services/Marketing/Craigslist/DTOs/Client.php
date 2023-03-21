<?php

namespace App\Services\Marketing\Craigslist\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use Carbon\Carbon;

/**
 * Class Client
 * 
 * @package App\Services\Marketing\Craigslist\DTOs
 */
class Client
{
    use WithConstructor, WithGetter;

    /**
     * @const int elapsed time
     */
    const MAX_ELAPSED = 60 * 60 * 7;


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
        $elapsed = time() - Carbon::parse($this->lastCheckin)->timestamp;

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
            return $behaviour->edit ?? false;
        }

        // Return Null
        return false;
    }


    /**
     * Get Client From Behaviour
     * 
     * @return Client
     */
    public static function fromBehaviour(Behaviour $behaviour): Client {
        // Get Client
        return new self([
            'dealer_id' => $behaviour->dealerId,
            'slot_id' => $behaviour->slotId,
            'uuid' => $behaviour->uuid,
            'version' => null,
            'label' => 'Offline ' . $behaviour->email,
            'last_ip' => null,
            'count' => 0,
            'last_checkin' => Carbon::now()->subSeconds(self::MAX_ELAPSED)->toDateTimeString()
        ]);
    }
}