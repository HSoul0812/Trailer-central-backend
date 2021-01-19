<?php

namespace App\Exceptions\CRM\Leads\Import;

/**
 * Class InvalidAdfImportFormatException
 *
 * Use this instead of \Exception to throw any kind of missing adf email access token
 *
 * @package App\Exceptions\CRM\Leads\Import
 */
class MissingAdfEmailAccessTokenException extends \Exception
{

    protected $message = 'An exception occurred trying to parse ADF email access token.';

}