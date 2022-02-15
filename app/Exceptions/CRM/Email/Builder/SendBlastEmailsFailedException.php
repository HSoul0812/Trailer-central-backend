<?php

namespace App\Exceptions\CRM\Email\Builder;

/**
 * Class SendBlastEmailsFailedException
 *
 * Use this instead of \Exception to throw any kind of blast emails failed to send exception
 *
 * @package App\Exceptions\CRM\Email\Builder
 */
class SendBlastEmailsFailedException extends \Exception
{

    protected $message = 'An unknown error occurred, preventing any blast emails from sending out!';

}