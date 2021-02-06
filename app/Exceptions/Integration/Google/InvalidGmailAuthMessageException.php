<?php

namespace App\Exceptions\Integration\Google;

/**
 * Class InvalidGmailAuthMessageException
 *
 * Use this instead of \Exception to throw any kind of invalid authentication sending gmail email
 *
 * @package App\Exceptions\Integration\Google
 */
class InvalidGmailAuthMessageException extends \Exception
{
    
    protected $message = 'Invalid authentication while trying to send Gmail email!';

}