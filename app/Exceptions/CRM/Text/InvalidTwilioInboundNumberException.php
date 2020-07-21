<?php

namespace App\Exceptions\CRM\Text;

/**
 * Class InvalidTwilioInboundNumber
 *
 * Use this instead of \Exception to throw any kind of "twilio invalid inbound number" exception
 *
 * @package App\Exceptions\CRM\Text
 */
class InvalidTwilioInboundNumberException extends \Exception
{
    
    protected $message = 'Tried to send text via twilio from an invalid number!';

}