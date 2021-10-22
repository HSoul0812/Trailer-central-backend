<?php

namespace App\Exceptions\CRM\Interactions\Facebook;

/**
 * Class FailedSendFacebookMessagetException
 *
 * Use this instead of \Exception to throw any kind of failed to send facebook message exception
 *
 * @package App\Exceptions\CRM\Interactions\Facebook
 */
class FailedSendFacebookMessagetException extends \Exception
{

    protected $message = 'An unknown error occurred trying to send facebook message.'; 

}