<?php

namespace App\Exceptions\CRM\Text;

/**
 * Class NoBlastSmsFromNumberException
 *
 * Use this instead of \Exception to throw any kind of missing blast SMS number-related exception
 *
 * @package App\Exceptions\CRM\Text
 */
class NoBlastSmsFromNumberException extends \Exception
{
    
    protected $message = 'Could not find from sms number for blast!';

}