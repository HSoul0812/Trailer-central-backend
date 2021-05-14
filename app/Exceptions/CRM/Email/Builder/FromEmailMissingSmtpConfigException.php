<?php

namespace App\Exceptions\CRM\Email\Builder;

/**
 * Class FromEmailMissingSmtpConfigException
 *
 * Use this instead of \Exception to throw any kind of from email not having SmtpConfig
 *
 * @package App\Exceptions\CRM\Email\Builder
 */
class FromEmailMissingSmtpConfigException extends \Exception
{

    protected $message = 'No SmtpConfig information was found for requested from email!';

}