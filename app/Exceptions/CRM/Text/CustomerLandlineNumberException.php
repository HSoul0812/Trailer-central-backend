<?php

namespace App\Exceptions\CRM\Text;

/**
 * Class NoTwilioNumberAvailableException
 *
 * Use this instead of \Exception to throw any kind of customer number being unable to receive texts exception
 *
 * @package App\Exceptions\CRM\Text
 */
class CustomerLandlineNumberException extends \Exception
{
    
    protected $message = 'The number provided is a landline and cannot receive texts!';

}