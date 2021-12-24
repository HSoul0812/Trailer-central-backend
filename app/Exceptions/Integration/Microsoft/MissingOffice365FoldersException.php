<?php

namespace App\Exceptions\Integration\Microsoft;

/**
 * Class MissingOffice 365LabelsException
 *
 * Use this instead of \Exception to throw any kind of missing Office 365 labels in Office 365 Azure
 *
 * @package App\Exceptions\Integration\Microsoft
 */
class MissingOffice 365LabelsException extends \Exception
{
    
    protected $message = 'Could not find any Labels on the Office 365 account, something is SERIOUSLY wrong!';

}