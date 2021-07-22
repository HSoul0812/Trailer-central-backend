<?php

namespace App\Exceptions\Integration\Microsoft;

/**
 * Class FailedInitializeOffice365MessageException
 *
 * Use this instead of \Exception to throw any kind of failed to generate Office 365 Message on Microsoft Azure
 *
 * @package App\Exceptions\Integration\Microsoft
 */
class FailedInitializeOffice365MessageException extends \Exception
{
    
    protected $message = 'Failed to create Office 365 message to send via Microsoft Azure!';

}