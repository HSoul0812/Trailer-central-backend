<?php

namespace App\Exceptions\CRM\Leads;

/**
 * Class AutoAssignJobMissingLeadException
 *
 * Use this instead of \Exception to throw any kind of auto assign job missing lead
 *
 * @package App\Exceptions\CRM\Leads
 */
class AutoAssignJobMissingLeadException extends \Exception
{

    protected $message = 'Lead was missing while trying to dispatch Auto Assign Job!!'; 

}