<?php

namespace App\Exceptions\Integration\Microsoft;

/**
 * Class MissingAzureAccessTokenException
 *
 * Use this instead of \Exception to throw any kind of missing access token on Microsoft Azure
 *
 * @package App\Exceptions\Integration\Microsoft
 */
class MissingAzureAccessTokenException extends \Exception
{
    
    protected $message = 'Microsoft Azure access token does not exist!';

}