<?php

namespace App\Exceptions\CRM\Interactions\Facebook;

/**
 * Class WrongFacebookMessageWindowException
 *
 * Use this instead of \Exception to throw any kind of facebook message sent outside of wrong window error
 *
 * @package App\Exceptions\CRM\Interactions\Facebook
 */
class WrongFacebookMessageWindowException extends \Exception
{

    protected $message = 'Facebook Message was sent outside of allowed window.'; 

}