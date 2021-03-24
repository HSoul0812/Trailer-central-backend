<?php

namespace App\Exceptions\Integration\Facebook;

/**
 * Class FailedReceivingLongLivedTokenException
 *
 * Use this instead of \Exception to throw any kind of error getting long-lived token on Facebook SDK
 *
 * @package App\Exceptions\Integration\Facebook
 */
class FailedReceivingLongLivedTokenException extends \Exception
{
    
    protected $message = 'An error occurred trying to exchange access token for long-lived token!';

}