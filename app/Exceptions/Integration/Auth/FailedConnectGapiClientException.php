<?php

namespace App\Exceptions\Integration\Auth;

/**
 * Class FailedConnectGapiClientException
 *
 * Use this instead of \Exception to throw any kind of failed to connect to Google API
 *
 * @package App\Exceptions\CRM\Text
 */
class FailedConnectGapiClientException extends \Exception
{
    
    protected $message = 'Failed to connect to Google API!';

}