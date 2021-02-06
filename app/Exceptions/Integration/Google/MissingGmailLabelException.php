<?php

namespace App\Exceptions\Integration\Google;

/**
 * Class MissingGmailLabelException
 *
 * Use this instead of \Exception to throw any kind of missing specific gmail label in Gmail API
 *
 * @package App\Exceptions\Integration\Google
 */
class MissingGmailLabelException extends \Exception
{
    
    protected $message = 'Could not find the requested label in Gmail account!';

}