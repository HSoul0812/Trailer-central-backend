<?php

namespace App\Service\Marketing\Craigslist\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class ClientMessage
 * 
 * @package App\Service\Marketing\Craigslist\DTOs
 */
class ClientMessage
{
    use WithConstructor, WithGetter;


    /**
     * @const string
     */
    const LAST_SENT_KEY = 'client:last-warning';


    /**
     * @const string
     */
    const CHECKIN_NOTICE = 'There are currently :total active :email Craigslist clients running at this time.';

    /**
     * @const string
     */
    const WARNING_LOW_CLIENTS = 'WARNING: The number of active Craigslist clients for :email has ' .
            'dropped down to :active, please check to ensure Craigslist posts do not fall too far behind.';

    /**
     * @const string
     */
    const WARNING_ELAPSED = 'WARNING: No :email Craigslist clients have checked in for over :minutes' .
            'minutes, please check to ensure Craigslist posts do not fall too far behind.';

    /**
     * @const string
     */
    const WARNING_HIGH = 'WARNING: Its has been over :minutes minutes since any :email Craigslist' .
            'clients have checked in! This must be reviewed as soon as possible!';

    /**
     * @const string
     */
    const WARNING_CRITICAL = 'CRITICAL!: Its has been over :minutes minutes since any :email Craigslist' .
            'clients have checked in! This must be fixed IMMEDIATELY!';


    /**
     * @var int
     */
    private $dealerId;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $level;

    /**
     * @var string
     */
    private $message;
}