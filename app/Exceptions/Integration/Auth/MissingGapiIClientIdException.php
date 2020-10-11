<?php

namespace App\Exceptions\Integration\Auth;

/**
 * Class MissingGapiClientIdException
 *
 * Use this instead of \Exception to throw any kind of missing Client ID on Google API
 *
 * @package App\Exceptions\CRM\Text
 */
class MissingGapiClientIdException extends \Exception
{
    
    protected $message = 'Google API Client ID does not exist!';

}