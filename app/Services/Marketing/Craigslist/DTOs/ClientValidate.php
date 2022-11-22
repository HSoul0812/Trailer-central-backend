<?php

namespace App\Service\Marketing\Craigslist\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class ClientValidate
 * 
 * @package App\Service\Marketing\Craigslist\DTOs
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
}