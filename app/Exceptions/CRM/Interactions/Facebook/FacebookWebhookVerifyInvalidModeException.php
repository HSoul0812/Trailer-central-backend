<?php

namespace App\Exceptions\CRM\Interactions\Facebook;

/**
 * Class FacebookWebhookVerifyInvalidModeException
 *
 * Use this instead of \Exception to throw any kind of facebook webhook verification invalid mode error
 *
 * @package App\Exceptions\CRM\Interactions\Facebook
 */
class FacebookWebhookVerifyInvalidModeException extends \Exception
{

    protected $message = 'Facebook Webhook failed to verify: provided mode is not valid.'; 

}