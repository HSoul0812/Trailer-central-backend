<?php

namespace App\Exceptions\Integration\Facebook;

/**
 * Class ExpiredFacebookAccessTokenException
 *
 * Use this instead of \Exception to throw any kind of expired access token on Facebook SDK
 *
 * @package App\Exceptions\Integration\Facebook
 */
class ExpiredFacebookAccessTokenException extends \Exception
{
    
    protected $message = 'Facebook access token has expired!';

}