<?php

namespace App\Exceptions\CRM\Text;

/**
 * Class NoCampaignSmsFromNumberException
 *
 * Use this instead of \Exception to throw any kind of missing blast SMS number-related exception
 *
 * @package App\Exceptions\CRM\Text
 */
class NoCampaignSmsFromNumberException extends \Exception
{
    protected $message = 'No From SMS Number Available';
}
