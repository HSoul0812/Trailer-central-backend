<?php

namespace App\Exceptions\Integration\Microsoft;

/**
 * Class InvalidOffice365AuthMessageException
 *
 * Use this instead of \Exception to throw any kind of invalid authentication sending Office 365 email
 *
 * @package App\Exceptions\Integration\Microsoft
 */
class InvalidOffice365AuthMessageException extends \Exception
{
    
    protected $message = 'Invalid authentication while trying to send Office 365 email!';

}