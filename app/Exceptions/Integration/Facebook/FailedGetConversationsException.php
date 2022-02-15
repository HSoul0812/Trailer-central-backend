<?php

namespace App\Exceptions\Integration\Facebook;

/**
 * Class FailedGetConversationsException
 *
 * Use this instead of \Exception to throw any kind of error getting conversations for facebook page
 *
 * @package App\Exceptions\Integration\Facebook
 */
class FailedGetConversationsException extends \Exception
{
    
    protected $message = 'An error occurred trying to get conversations for the page!';

}