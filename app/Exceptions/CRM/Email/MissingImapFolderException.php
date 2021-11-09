<?php

namespace App\Exceptions\CRM\Email;

/**
 * Class MissingImapFolderException
 *
 * Use this instead of \Exception to throw any kind of missing imap folder
 *
 * @package App\Exceptions\CRM\Email
 */
class MissingImapFolderException extends \Exception
{
    
    protected $message = 'Could not find provided IMAP folder!';

}