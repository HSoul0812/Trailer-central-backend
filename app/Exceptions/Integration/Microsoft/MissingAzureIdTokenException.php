<?php

namespace App\Exceptions\Integration\Microsoft;

/**
 * Class MissingAzureIdTokenException
 *
 * Use this instead of \Exception to throw any kind of missing ID token on Microsoft Azure
 *
 * @package App\Exceptions\Integration\Microsoft
 */
class MissingAzureIdTokenException extends \Exception
{
    
    protected $message = 'Microsoft Azure ID token does not exist!';

}