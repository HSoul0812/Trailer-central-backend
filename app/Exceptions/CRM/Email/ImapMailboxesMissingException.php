<?php

namespace App\Exceptions\CRM\Email;

/**
 * Class ImapMailboxesMissingException
 *
 * Use this instead of \Exception to throw any kind of missing imap mailboxes exception
 *
 * @package App\Exceptions\CRM\Email
 */
class ImapMailboxesMissingException extends \Exception
{

    protected $message = 'Could not find mailboxes from IMAP.';

}