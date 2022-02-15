<?php

namespace App\Exceptions\CRM\Email;

/**
 * Class SaveNtlmFailedException
 *
 * Use this instead of \Exception to throw any kind of save NTLM email failed exception
 *
 * @package App\Exceptions\CRM\Email
 */
class SaveNtlmFailedException extends \Exception
{

    protected $message = 'An error occurred trying to save the NTLM email!'; 

}