<?php

namespace App\Exceptions\CRM\Email;

/**
 * Class SendNtlmFailedException
 *
 * Use this instead of \Exception to throw any kind of send NTLM email failed exception
 *
 * @package App\Exceptions\CRM\Email
 */
class SendNtlmFailedException extends \Exception
{

    protected $message = 'An error occurred trying to send the NTLM email!'; 

}