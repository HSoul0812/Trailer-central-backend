<?php

namespace App\Exceptions\CRM\Text;

/**
 * Class NoLeadSmsNumberAvailableException
 *
 * Use this instead of \Exception to throw any kind of missing lead SMS number-related exception
 *
 * @package App\Exceptions\CRM\Text
 */
class NoLeadSmsNumberAvailableException extends \Exception
{
    
    protected $message = 'Could not find sms number for lead!';

}