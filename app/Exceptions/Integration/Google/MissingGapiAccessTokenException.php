<?php

namespace App\Exceptions\Integration\Google;

/**
 * Class MissingGapiAccessTokenException
 *
 * Use this instead of \Exception to throw any kind of missing access token on Google API
 *
 * @package App\Exceptions\Integration\Google
 */
class MissingGapiAccessTokenException extends \Exception
{
    
    protected $message = 'Google API access token does not exist!';

}