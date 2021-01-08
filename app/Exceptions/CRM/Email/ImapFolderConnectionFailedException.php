<?php

namespace App\Exceptions\CRM\Email;

/**
 * Class ImapFolderConnectionFailedException
 *
 * Use this instead of \Exception to throw any kind of imap folder connection failed exception
 *
 * @package App\Exceptions\CRM\Email
 */
class ImapFolderConnectionFailedException extends \Exception
{

    protected $message = 'An exception occurred trying to connect to IMAP folder.';

}