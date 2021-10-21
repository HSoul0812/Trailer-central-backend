<?php

namespace App\Exceptions\CRM\Interactions\Facebook;

/**
 * Class FacebookWebhookVerifyMismatchException
 *
 * Use this instead of \Exception to throw any kind of facebook webhook verification mismatch error
 *
 * @package App\Exceptions\CRM\Interactions\Facebook
 */
class FacebookWebhookVerifyMismatchException extends \Exception
{

    protected $message = 'Facebook Webhook failed to verify: provided verification code does not match.'; 

}