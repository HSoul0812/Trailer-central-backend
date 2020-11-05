<?php

namespace App\Exceptions\Integration\Facebook;

/**
 * Class FailedCreateProductFeedException
 *
 * Use this instead of \Exception to throw any kind of missing access token on Google API
 *
 * @package App\Exceptions\Integration\Facebook
 */
class FailedCreateProductFeedException extends \Exception
{
    
    protected $message = 'An error occurred trying to create product feed!';

}