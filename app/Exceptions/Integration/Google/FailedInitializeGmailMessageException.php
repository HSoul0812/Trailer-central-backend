<?php

namespace App\Exceptions\Integration\Google;

/**
 * Class FailedInitializeGmailMessageException
 *
 * Use this instead of \Exception to throw any kind of failed to generate Gmail Message on Google API
 *
 * @package App\Exceptions\Integration\Google
 */
class FailedInitializeGmailMessageException extends \Exception
{
    
    protected $message = 'Failed to create gmail message to send via Google API!';

}