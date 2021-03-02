<?php

namespace App\Exceptions\Integration\Facebook;

/**
 * Class FailedValidateAccessTokenException
 *
 * Use this instead of \Exception to throw any kind of error validating access token on Facebook SDK
 *
 * @package App\Exceptions\Integration\Facebook
 */
class FailedValidateAccessTokenException extends \Exception
{
    
    protected $message = 'An error occurred trying to validate the provided access token!';

}