<?php

namespace App\Exceptions\CRM\Email\Builder;

/**
 * Class SendCampaignEmailsFailedException
 *
 * Use this instead of \Exception to throw any kind of campaign emails failed to send exception
 *
 * @package App\Exceptions\CRM\Email\Builder
 */
class SendCampaignEmailsFailedException extends \Exception
{

    protected $message = 'An unknown error occurred, preventing any campaign emails from sending out!';

}