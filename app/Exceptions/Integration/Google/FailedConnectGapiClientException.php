<?php

namespace App\Exceptions\Integration\Google;

/**
 * Class FailedConnectGapiClientException
 *
 * Use this instead of \Exception to throw any kind of failed to connect to Google API
 *
 * @package App\Exceptions\Integration\Google
 */
class FailedConnectGapiClientException extends \Exception
{
    
    protected $message = 'Failed to connect to Google API!';

}