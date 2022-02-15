<?php

namespace App\Exceptions\CRM\Email\Builder;

/**
 * Class SendBuilderEmailsFailedException
 *
 * Use this instead of \Exception to throw any kind of email builder emails failed to send exception
 *
 * @package App\Exceptions\CRM\Email\Builder
 */
class SendBuilderEmailsFailedException extends \Exception
{

    protected $message = 'An unknown error occurred, preventing any email builder emails from sending out!';

}