<?php

namespace App\Exceptions\CRM\Leads\Import;

/**
 * Class InvalidAdfImportVendorException
 *
 * Use this instead of \Exception to throw any kind of vendor mismatch error
 *
 * @package App\Exceptions\CRM\Leads\Import
 */
class InvalidAdfImportVendorException extends \Exception
{

    protected $message = 'Could not match Vendor to Dealer during ADF Import!';

}