<?php

namespace App\Exceptions\CRM\Email\Builder;

/**
 * Class SendBuilderEmailsFailedException
 *
 * Use this instead of \Exception to throw any kind of email builder job failed exception
 *
 * @package App\Exceptions\CRM\Email\Builder
 */
class SendEmailBuilderJobFailedException extends \Exception
{

    protected $message = 'An unknown error occurred on email builder job!';

}