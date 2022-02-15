<?php

namespace App\Exceptions\CRM\Leads;

/**
 * Class SendInquiryFailedException
 *
 * Use this instead of \Exception to throw any kind of send inquiry failed exception
 *
 * @package App\Exceptions\CRM\Leads
 */
class SendInquiryFailedException extends \Exception
{

    protected $message = 'An error occurred trying to send the lead inquiry!'; 

}