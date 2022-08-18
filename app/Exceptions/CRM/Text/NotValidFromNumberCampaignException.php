<?php

namespace App\Exceptions\CRM\Text;

/**
 * Class NotValidFromNumberCampaignException
 * @package App\Exceptions\CRM\Text
 */
class NotValidFromNumberCampaignException extends \InvalidArgumentException
{
    protected $message = 'From SMS Number is Invalid';
}
