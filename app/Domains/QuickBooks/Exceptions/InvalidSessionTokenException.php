<?php

namespace App\Domains\QuickBooks\Exceptions;

use Exception;

class InvalidSessionTokenException extends Exception
{
    public static function make(int $dealerId): InvalidSessionTokenException
    {
        return new static("Dealer $dealerId has in invalid session token!");
    }
}
