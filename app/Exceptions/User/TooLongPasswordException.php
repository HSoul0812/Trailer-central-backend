<?php

declare(strict_types=1);

namespace App\Exceptions\User;

class TooLongPasswordException extends \DomainException
{
    protected $code = 400;
    protected $message = 'The password length is too long!';
}
