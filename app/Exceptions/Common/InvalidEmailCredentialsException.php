<?php

namespace App\Exceptions\Common;

/**
 * Class InvalidEmailCredentialsException
 *
 * Use this instead of \Exception to throw any kind of invalid email credentials exception
 *
 * @package App\Exceptions\Common
 */
class InvalidEmailCredentialsException extends \Exception
{
    
    protected $message = 'Credentials to retrieve emails were not valid.';

}