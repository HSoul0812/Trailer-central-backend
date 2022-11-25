<?php

namespace App\Exceptions;

use Exception;

class EmptyPropValueException extends Exception
{
    public static function make(string $propName): EmptyPropValueException
    {
        return new EmptyPropValueException("Prop value $propName can't be empty.");
    }
}
