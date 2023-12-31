<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use JetBrains\PhpStorm\Pure;
use Throwable;

class PropertyDoesNotExist extends Exception
{
    #[Pure]
    public function __construct($message = 'The property does not exist', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
