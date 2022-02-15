<?php

namespace App\Exceptions\CRM\Email\Builder;

/**
 * Class SendTemplateEmailFailedException
 *
 * Use this instead of \Exception to throw any kind of template email failed to send exception
 *
 * @package App\Exceptions\CRM\Email\Builder
 */
class SendTemplateEmailFailedException extends \Exception
{

    protected $message = 'An unknown error occurred, preventing the template email from sending out!';

}