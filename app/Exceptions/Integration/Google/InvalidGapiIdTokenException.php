<?php

namespace App\Exceptions\Integration\Google;

/**
 * Class InvalidGapiIdTokenException
 *
 * Use this instead of \Exception to throw any kind of invalid ID token on Google API
 *
 * @package App\Exceptions\Integration\Google
 */
class InvalidGapiIdTokenException extends \Exception
{
    
    protected $message = 'Google API ID token is not valid!';

}