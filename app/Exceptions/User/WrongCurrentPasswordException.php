<?php

declare(strict_types=1);

namespace App\Exceptions\User;

class WrongCurrentPasswordException extends \DomainException
{
    protected $code = 400;
    protected $message = 'The current password is wrong!';
}
