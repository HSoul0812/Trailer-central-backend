<?php

namespace App\Exceptions\Integration\Facebook;

/**
 * Class FailedCreateProductFeedException
 *
 * Use this instead of \Exception to throw any kind of error getting product feed on Facebook SDK
 *
 * @package App\Exceptions\Integration\Facebook
 */
class FailedGetProductFeedException extends \Exception
{
    
    protected $message = 'An error occurred trying to get product feed!';

}