<?php

namespace App\Exceptions\CRM\Text;

/**
 * Class SendTwilioTextFailedException
 *
 * Use this instead of \Exception to throw any kind of "send twilio text failed" exception
 *
 * @package App\Exceptions\CRM\Text
 */
class SendTwilioTextFailedException extends \Exception
{
    
    protected $message = 'Failed to send a text over twilio!'; 

}