<?php

namespace App\Exceptions\Integration\Google;

/**
 * Class InvalidGoogleAuthCodeException
 *
 * Use this instead of \Exception to throw any kind of invalid authentication code for google
 *
 * @package App\Exceptions\Integration\Google
 */
class InvalidGoogleAuthCodeException extends \Exception
{
    
    protected $message = 'Invalid auth code while trying to get google access token!';

}