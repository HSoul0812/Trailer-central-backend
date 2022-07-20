<?php

namespace App\Exceptions\Integration\Microsoft;

/**
 * Class CannotReceiveOffice365MessagesException
 *
 * Use this instead of \Exception to throw any kind of error accessing messages on Microsoft Azure
 *
 * @package App\Exceptions\Integration\Microsoft
 */
class CannotReceiveOffice365MessagesException extends \Exception
{
    
    protected $message = 'Error occurred trying to receive Office 365 messages!';

}