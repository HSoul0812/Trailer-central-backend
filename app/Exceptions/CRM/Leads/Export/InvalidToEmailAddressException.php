<?php

namespace App\Exceptions\CRM\Leads\Export;

use Exception;

/**
 * Class InvalidToEmailAddressException
 *
 * Use this instead of \Exception to throw any kind of missing adf Dealer ID exception
 *
 * @package App\Exceptions\CRM\Leads\Import
 */
class InvalidToEmailAddressException extends Exception
{

    protected $message = 'Invalid to email address when exporting via ADF Service!';

}
