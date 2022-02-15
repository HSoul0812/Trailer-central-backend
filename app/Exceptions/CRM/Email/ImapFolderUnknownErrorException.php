<?php

namespace App\Exceptions\CRM\Email;

/**
 * Class ImapFolderUnknownErrorException
 *
 * Use this instead of \Exception to throw any kind of unknown imap folder connection error exception
 *
 * @package App\Exceptions\CRM\Email
 */
class ImapFolderUnknownErrorException extends \Exception
{

    protected $message = 'An unknown error occurred trying to connect to IMAP.';

}