<?php

namespace App\Exceptions\Integration\Facebook;

/**
 * Class MissingFacebookAccessTokenException
 *
 * Use this instead of \Exception to throw any kind of missing access token on Facebook SDK
 *
 * @package App\Exceptions\Integration\Facebook
 */
class MissingFacebookAccessTokenException extends \Exception
{
    
    protected $message = 'Facebook access token does not exist!';

}