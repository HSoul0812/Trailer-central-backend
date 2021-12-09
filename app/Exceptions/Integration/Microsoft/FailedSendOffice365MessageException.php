<?php

namespace App\Exceptions\Integration\Microsoft;

/**
 * Class FailedSendOffice365MessageException
 *
 * Use this instead of \Exception to throw any kind of failed to send Office 365 email via Microsoft Azure
 *
 * @package App\Exceptions\Integration\Microsoft
 */
class FailedSendOffice365MessageException extends \Exception
{
    
    protected $message = 'Failed to send email via Office 365 Azure!';

}