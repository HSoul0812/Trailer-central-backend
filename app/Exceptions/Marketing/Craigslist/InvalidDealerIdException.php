<?php

namespace App\Exceptions\Marketing\Craigslist;

use Exception;

/**
 * Class InvalidDealerIdException
 *
 * Use this instead of \Exception to throw any kind of missing adf Dealer ID exception
 *
 * @package App\Exceptions\CRM\Leads\Import
 */
class InvalidDealerIdException extends Exception
{

    protected $message = 'Dealer ID is necessary to retrieve the information.';

}
