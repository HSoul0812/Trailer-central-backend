<?php

namespace App\Exceptions\CRM\Leads\Import;

/**
 * Class InvalidDealerIdException
 *
 * Use this instead of \Exception to throw any kind of missing adf Dealer ID exception
 *
 * @package App\Exceptions\CRM\Leads\Import
 */
class InvalidDealerIdException extends \Exception
{

    protected $message = 'Could not match adf import email address to dealer.';

}
