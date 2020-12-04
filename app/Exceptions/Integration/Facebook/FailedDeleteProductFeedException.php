<?php

namespace App\Exceptions\Integration\Facebook;

/**
 * Class FailedDeleteProductFeedException
 *
 * Use this instead of \Exception to throw any kind of error deleting product feed on Facebook SDK
 *
 * @package App\Exceptions\Integration\Facebook
 */
class FailedDeleteProductFeedException extends \Exception
{
    
    protected $message = 'An error occurred trying to delete product feed!';

}