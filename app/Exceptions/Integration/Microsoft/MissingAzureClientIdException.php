<?php

namespace App\Exceptions\Integration\Microsoft;

/**
 * Class MissingAzureClientIdException
 *
 * Use this instead of \Exception to throw any kind of missing Client ID on Microsoft Azure
 *
 * @package App\Exceptions\Integration\Microsoft
 */
class MissingAzureClientIdException extends \Exception
{
    
    protected $message = 'Microsoft Azure Client ID does not exist!';

}