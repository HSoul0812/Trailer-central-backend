<?php

namespace App\Exceptions\CRM\Email;

/**
 * Class SaveNtlmAttachmentsFailedException
 *
 * Use this instead of \Exception to throw any kind of save NTLM attachments email failed exception
 *
 * @package App\Exceptions\CRM\Email
 */
class SaveNtlmAttachmentsFailedException extends \Exception
{

    protected $message = 'An error occurred trying to save the NTLM attachments email!'; 

}