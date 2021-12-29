<?php

namespace App\Exceptions\CRM\Interactions\Facebook;

/**
 * Class FacebookLeadDoesntExistException
 *
 * Use this instead of \Exception to throw any kind of facebook lead doesn't exist exception
 *
 * @package App\Exceptions\CRM\Interactions\Facebook
 */
class FacebookLeadDoesntExistException extends \Exception
{

    protected $message = 'The Facebook user does not exist.'; 

}