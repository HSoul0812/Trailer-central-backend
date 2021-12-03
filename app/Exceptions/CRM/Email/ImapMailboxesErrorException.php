<?php

namespace App\Exceptions\CRM\Email;

/**
 * Class ImapMailboxesErrorException
 *
 * Use this instead of \Exception to throw any kind of unknown imap mailboxes error exception
 *
 * @package App\Exceptions\CRM\Email
 */
class ImapMailboxesErrorException extends \Exception
{

    protected $message = 'An unknown error occurred trying to get imap mailboxes.';

}