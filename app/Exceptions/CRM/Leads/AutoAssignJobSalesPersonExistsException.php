<?php

namespace App\Exceptions\CRM\Leads;

/**
 * Class AutoAssignJobSalesPersonExistsException
 *
 * Use this instead of \Exception to throw any kind of auto assign job lead already has sales person assigned
 *
 * @package App\Exceptions\CRM\Leads
 */
class AutoAssignJobSalesPersonExistsException extends \Exception
{

    protected $message = 'Lead is ALREADY assigned a sales person while trying to dispatch Auto Assign Job!!'; 

}