<?php

namespace App\Exceptions\Integration\Auth;

/**
 * Class InvalidAuthCodeTokenTypeException
 *
 * Use this instead of \Exception to throw any kind of invalid token type on auth code
 *
 * @package App\Exceptions\Integration\Auth
 */
class InvalidAuthCodeTokenTypeException extends \Exception
{
    
    protected $message = 'The token type provided is not supported while getting auth code!';

}