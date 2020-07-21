<?php

namespace App\Exceptions\CRM\Text;

/**
 * Class CreateTwilioMessageException
 *
 * Use this instead of \Exception to throw any kind of "create twilio message" exception
 *
 * @package App\Exceptions\CRM\Text
 */
class CreateTwilioMessageException extends \Exception
{
    
    protected $message = 'Exception occurred trying to create a twilio message to send!'; 

}