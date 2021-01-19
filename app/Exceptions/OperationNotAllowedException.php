<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class OperationNotAllowedException extends Exception
{
    public function __construct($message = 'The operation is not allowed', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
