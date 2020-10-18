<?php

namespace App\Exceptions\Integration\Auth;

/**
 * Class InvalidGapiIdTokenException
 *
 * Use this instead of \Exception to throw any kind of invalid ID token on Google API
 *
 * @package App\Exceptions\Integration\Auth
 */
class InvalidGapiIdTokenException extends \Exception
{
    
    protected $message = 'Google API ID token is not valid!';

}