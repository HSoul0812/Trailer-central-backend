<?php

namespace App\Services\Dispatch\Facebook\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class CommonToken
 * 
 * @package App\Services\Dispatch\Facebook\DTOs
 */
class MarketplaceStep
{
    use WithConstructor, WithGetter;

    /**
     * @const string
     */
    const LOG_PREFIX = 'Facebook Marketplace';

    /**
     * @const array<string>
     */
    const LOG_DESC = [
        'debug' => 'Debug',
        'info' => 'Log',
        'error' => 'Error'
    ];


    /**
     * @var string
     */
    private $psr;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $date;


    /**
     * Get Response Message for Step
     * 
     * @return string
     */
    public function getLogString(): string {
        // Initialize Strirng
        $prefix = self::LOG_PREFIX;

        // Get Description
        $desc = self::LOG_DESC[$this->psr];

        // Compile Final Log
        return $prefix . ' ' . $desc . ' at ' . $this->date . ': ' . $this->message;
    }
}