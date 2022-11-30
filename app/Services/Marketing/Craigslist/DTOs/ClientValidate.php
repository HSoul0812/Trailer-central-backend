<?php

namespace App\Services\Marketing\Craigslist\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class ClientValidate
 * 
 * @package App\Services\Marketing\Craigslist\DTOs
 */
class ClientValidate
{
    use WithConstructor, WithGetter;


    /**
     * @const array<string>
     */
    const WARNING_LEVELS = [
        'warning',
        'error',
        'critical'
    ];

    /**
     * @const string
     */
    const SCALE_HOURS = 'hours';

    /**
     * @const string
     */
    const SCALE_MINUTES = 'minutes';

    /**
     * @const string
     */
    const CLIENTS_LEVEL = 'clients';

    /**
     * @const string
     */
    const CRITICAL_LEVEL = 'critical';


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
    private $email;

    /**
     * @var string
     */
    private $label;

    /**
     * @var bool
     */
    private $isEdit;

    /**
     * @var string
     */
    private $level;

    /**
     * @var int
     */
    private $elapsed;


    /**
     * Check if Warning Exists
     * 
     * @return bool
     */
    public function isWarning(): bool {
        // Check if Level Exists
        return in_array($this->level, self::WARNING_LEVELS);
    }

    /**
     * Get Parsed Elapsed Time
     * 
     * @return string
     */
    public function elapsed(): string {
        // Elapsed > 60?
        if($this->elapsed > 60) {
            return floor($this->elapsed / 60);
        }

        // Get Minutes
        return $this->elapsed;
    }

    /**
     * Get Scale of Elapsed Time
     * 
     * @return string
     */
    public function scale(): string {
        // Elapsed > 60?
        if($this->elapsed > 60) {
            return self::SCALE_HOURS;
        }

        // Get Minutes
        return self::SCALE_MINUTES;
    }
}