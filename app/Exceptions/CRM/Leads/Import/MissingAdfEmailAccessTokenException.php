<?php

namespace App\Exceptions\CRM\Leads\Import;

/**
 * Class InvalidAdfImportFormatException
 *
 * Use this instead of \Exception to throw any kind of invalid lead adf import format
 *
 * @package App\Exceptions\CRM\Leads\Import
 */
class MissingAdfEmailAccessTokenException extends \Exception
{

    protected $message = 'An exception occurred trying to parse ADF import email; invalid ADF format.';

}