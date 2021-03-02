<?php

namespace App\Exceptions\Integration\Google;

/**
 * Class MissingGapiIdTokenException
 *
 * Use this instead of \Exception to throw any kind of missing ID token on Google API
 *
 * @package App\Exceptions\Integration\Google
 */
class MissingGapiIdTokenException extends \Exception
{
    
    protected $message = 'Google API ID token does not exist!';

}