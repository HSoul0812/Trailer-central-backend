<?php

namespace App\Exceptions\CRM\Leads\Import;

/**
 * Class MissingEmailAccessTokenException
 *
 * Use this instead of \Exception to throw any kind of invalid lead adf import format
 *
 * @package App\Exceptions\CRM\Leads\Import
 */
class MissingEmailAccessTokenException extends \Exception
{

    protected $message = 'An exception occurred trying to parse email access token.';

}
