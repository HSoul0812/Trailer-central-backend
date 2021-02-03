<?php

namespace App\Exceptions\Integration\Google;

/**
 * Class FailedSendGmailMessageException
 *
 * Use this instead of \Exception to throw any kind of failed to send gmail email via Google API
 *
 * @package App\Exceptions\Integration\Google
 */
class FailedSendGmailMessageException extends \Exception
{
    
    protected $message = 'Failed to send email via Gmail API!';

}