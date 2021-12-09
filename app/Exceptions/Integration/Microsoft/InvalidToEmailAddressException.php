<?php

namespace App\Exceptions\Integration\Microsoft;

/**
 * Class InvalidToEmailAddressException
 *
 * Use this instead of \Exception to throw any kind of invalid to email address sending via Office 365
 *
 * @package App\Exceptions\Integration\Microsoft
 */
class InvalidToEmailAddressException extends \Exception
{
    
    protected $message = 'Invalid to email address when sending via Office 365 Service!';

}