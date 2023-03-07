<?php

namespace App\Exceptions\CRM\Leads;

/**
 * Class MissingLeadIdGetAllTradesException
 *
 * Use this instead of \Exception to throw any kind of missing lead id on get all trades exception
 *
 * @package App\Exceptions\CRM\Leads
 */
class MissingLeadIdGetAllTradesException extends \Exception
{

    protected $message = 'The column lead_id is REQUIRED to get all trades, but was not provided!'; 

}