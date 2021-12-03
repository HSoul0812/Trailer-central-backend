<?php

namespace App\Exceptions\CRM\Interactions\Facebook;

/**
 * Class InvalidFacebookWebhookObjectException
 *
 * Use this instead of \Exception to throw any kind of facebook webhook invalid object error
 *
 * @package App\Exceptions\CRM\Interactions\Facebook
 */
class InvalidFacebookWebhookObjectException extends \Exception
{

    protected $message = 'Facebook Webhook object is not valid.'; 

}