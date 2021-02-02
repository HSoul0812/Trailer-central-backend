<?php

namespace App\Exceptions\Integration\Google;

/**
 * Class InvalidGmailAuthMessageException
 *
 * Use this instead of \Exception to throw any kind of invalid authentication code for google
 *
 * @package App\Exceptions\Integration\Auth
 */
class InvalidAuthCodeException extends \Exception
{
    
    protected $message = 'Invalid auth code while trying to get google access token!';

}