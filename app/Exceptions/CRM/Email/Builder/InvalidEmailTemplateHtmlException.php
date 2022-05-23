<?php

namespace App\Exceptions\CRM\Email\Builder;

/**
 * Class InvalidEmailTemplateHtmlException
 *
 * Use this instead of \Exception to throw any kind of "invalid email template" exception
 *
 * @package App\Exceptions\CRM\Email\Builder
 */
class InvalidEmailTemplateHtmlException extends \Exception
{

    protected $message = 'Tried to send a blast with an invalid email template!';

}