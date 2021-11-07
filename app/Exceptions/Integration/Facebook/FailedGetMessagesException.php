<?php

namespace App\Exceptions\Integration\Facebook;

/**
 * Class FailedGetMessagesException
 *
 * Use this instead of \Exception to throw any kind of error getting messages for facebook page
 *
 * @package App\Exceptions\Integration\Facebook
 */
class FailedGetMessagesException extends \Exception
{
    
    protected $message = 'An error occurred trying to get messages for the page!';

}