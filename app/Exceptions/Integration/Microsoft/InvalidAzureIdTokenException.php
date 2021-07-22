<?php

namespace App\Exceptions\Integration\Microsoft;

/**
 * Class InvalidAzureIdTokenException
 *
 * Use this instead of \Exception to throw any kind of invalid ID token on Microsoft Azure
 *
 * @package App\Exceptions\Integration\Microsoft
 */
class InvalidAzureIdTokenException extends \Exception
{
    
    protected $message = 'Microsoft Azure ID token is not valid!';

}