<?php

namespace App\Exceptions\Integration\Google;

/**
 * Class InvalidToEmailAddressException
 *
 * Use this instead of \Exception to throw any kind of invalid to email address sending via gmail
 *
 * @package App\Exceptions\Integration\Google
 */
class InvalidToEmailAddressException extends \Exception
{
    
    protected $message = 'Invalid to email address when sending via Gmail Service!';

}