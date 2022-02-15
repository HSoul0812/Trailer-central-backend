<?php

namespace App\Exceptions\CRM\Email;

/**
 * Class ImapConnectionFailedException
 *
 * Use this instead of \Exception to throw any kind of imap connection failed exception
 *
 * @package App\Exceptions\CRM\Email
 */
class ImapConnectionFailedException extends \Exception
{

    protected $message = 'An exception occurred trying to connect to IMAP.';

}