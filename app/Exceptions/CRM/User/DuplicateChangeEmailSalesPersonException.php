<?php

namespace App\Exceptions\CRM\User;

/**
 * Class DuplicateChangeEmailSalesPersonException
 *
 * Use this instead of \Exception to throw any kind of "duplicate email when changing email"
 *
 * @package App\Exceptions\CRM\Email
 */
class DuplicateChangeEmailSalesPersonException extends \Exception
{

    protected $message = 'Cannot change email on sales person, another sales person already uses this email address.'; 

}