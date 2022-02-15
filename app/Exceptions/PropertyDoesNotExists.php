<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class PropertyDoesNotExists extends Exception
{
    public function __construct($message = 'The property does not exists', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
