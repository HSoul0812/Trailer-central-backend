<?php

namespace App\Exceptions\Integration\Facebook;

/**
 * Class FailedGetFacebookAccojuntsException
 *
 * Use this instead of \Exception to throw any kind of error getting accounts on Facebook SDK
 *
 * @package App\Exceptions\Integration\Facebook
 */
class FailedGetFacebookAccojuntsException extends \Exception
{
    
    protected $message = 'An error occurred trying to get list of facebook accounts!';

}