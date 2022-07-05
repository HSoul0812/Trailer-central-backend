<?php

namespace App\Exceptions\CRM\Text;

/**
 * Class NoLeadsDeliverBlastException
 *
 * Use this instead of \Exception to throw any kind of "no leads on blast" exception
 *
 * @package App\Exceptions\CRM\Text
 */
class NoLeadsDeliverBlastException extends BlastException
{
    protected $message = 'No Leads to Send to Based on Your Chosen Criteria';
}
