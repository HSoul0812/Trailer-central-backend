<?php

namespace App\Exceptions\Integration\Microsoft;

/**
 * Class FailedConnectAzureClientException
 *
 * Use this instead of \Exception to throw any kind of failed to connect to Microsoft Azure
 *
 * @package App\Exceptions\Integration\Microsoft
 */
class FailedConnectAzureClientException extends \Exception
{
    
    protected $message = 'Failed to connect to Microsoft Azure!';

}