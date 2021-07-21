<?php

namespace App\Exceptions\Integration\Microsoft;

/**
 * Class MissingOffice365LabelException
 *
 * Use this instead of \Exception to throw any kind of missing specific Office 365 label in Office 365 Azure
 *
 * @package App\Exceptions\Integration\Microsoft
 */
class MissingOffice365LabelException extends \Exception
{
    
    protected $message = 'Could not find the requested label in Office 365 account!';

}