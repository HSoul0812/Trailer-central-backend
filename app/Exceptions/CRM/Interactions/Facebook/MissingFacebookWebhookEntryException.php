<?php

namespace App\Exceptions\CRM\Interactions\Facebook;

/**
 * Class MissingFacebookWebhookEntryException
 *
 * Use this instead of \Exception to throw any kind of facebook webhook entry error
 *
 * @package App\Exceptions\CRM\Interactions\Facebook
 */
class MissingFacebookWebhookEntryException extends \Exception
{

    protected $message = 'Facebook Webhook entry is not valid or missing.'; 

}