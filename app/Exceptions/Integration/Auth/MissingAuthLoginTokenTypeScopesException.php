<?php

namespace App\Exceptions\Integration\Auth;

/**
 * Class MissingGapiAccessTokenException
 *
 * Use this instead of \Exception to throw any kind of missing token type/scopes on auth login
 *
 * @package App\Exceptions\Integration\Auth
 */
class MissingAuthLoginTokenTypeScopesException extends \Exception
{
    
    protected $message = 'Token type and scopes are required to generate auth login URL!';

}