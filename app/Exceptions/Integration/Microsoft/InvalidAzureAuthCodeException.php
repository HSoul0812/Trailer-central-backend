<?php

namespace App\Exceptions\Integration\Microsoft;

/**
 * Class InvalidMicrosoftAuthCodeException
 *
 * Use this instead of \Exception to throw any kind of invalid authentication code for Microsoft
 *
 * @package App\Exceptions\Integration\Microsoft
 */
class InvalidMicrosoftAuthCodeException extends \Exception
{
    
    protected $message = 'Invalid auth code while trying to get Microsoft Azure access token!';

}