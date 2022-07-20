<?php

namespace App\Exceptions\CRM\Leads\Import;

/**
 * Class InvalidImportFormatException
 *
 * Use this instead of \Exception to throw any kind of missing adf email access token
 *
 * @package App\Exceptions\CRM\Leads\Import
 */
class InvalidImportFormatException extends \Exception
{

    protected $message = 'An exception occurred trying to parse ADF import email; invalid ADF format.';

}
