<?php

namespace App\Exceptions\Integration\Google;

/**
 * Class MissingGmailLabelsException
 *
 * Use this instead of \Exception to throw any kind of missing gmail labels in Gmail API
 *
 * @package App\Exceptions\Integration\Google
 */
class MissingGmailLabelsException extends \Exception
{
    
    protected $message = 'Could not find any Labels on the Gmail account, something is SERIOUSLY wrong!';

}