<?php

namespace App\Exceptions\Integration\Auth;

/**
 * Class MissingGapiClientIdException
 *
 * Use this instead of \Exception to throw any kind of missing Client ID on Google API
 *
 * @package App\Exceptions\Integration\Auth
 */
class MissingGapiClientIdException extends \Exception
{
    
    protected $message = 'Google API Client ID does not exist!';

}