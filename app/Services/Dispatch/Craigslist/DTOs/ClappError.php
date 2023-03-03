<?php

namespace App\Services\Dispatch\Craigslist\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class ClappError
 * 
 * @package App\Services\Dispatch\Craigslist\DTOs
 */
class ClappError
{
    use WithConstructor, WithGetter;


    /**
     * @const array<array{status: string,
     *                    state: string,
     *                    text_status: string}>
     */
    const ERROR_MESSAGES = [
        'pending-billing' => 'Waiting for billing...'
    ];


    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $textStatus;


    /**
     * Fill ClappError With Details Based on Error Message
     * 
     * @param string $error
     * @param array $params
     * @return ClappError
     */
    public static function fill(string $error, array $params) {
        // Get Standard
        $status = 'error';
        $state = $error;

        // Get Pending Billing
        if($error === 'pending-billing') {
            $status = $error;
            $state = 'billing-add-funds';
        }

        // Return Error
        return new ClappError([
            'status' => $status,
            'state' => $state,
            'text_status' => self::getError($error, $params)
        ]);
    }

    /**
     * Get Error Text And Fill Params
     * 
     * @param string $error
     * @return string
     */
    public static function getError(string $error, array $params): string {
        return self::ERROR_MESSAGES[$error];
    }
}