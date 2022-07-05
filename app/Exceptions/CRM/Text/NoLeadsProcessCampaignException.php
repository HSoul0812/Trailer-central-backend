<?php

namespace App\Exceptions\CRM\Text;

/**
 * Class NoLeadsProcessCampaignException
 *
 * Use this instead of \Exception to throw any kind of "no leads on campaign" exception
 *
 * @package App\Exceptions\CRM\Text
 */
class NoLeadsProcessCampaignException extends \Exception
{
    protected $message = 'No Leads to Send to Based on Your Chosen Criteria';
}
