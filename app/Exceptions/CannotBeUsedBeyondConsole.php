<?php

declare(strict_types=1);

namespace App\Exceptions;

use BadMethodCallException;
use JetBrains\PhpStorm\Pure;
use Throwable;

class CannotBeUsedBeyondConsole extends BadMethodCallException
{
    public const MESSAGE = 'Method or class cannot be used beyond the console';

    #[Pure]
    public function __construct($message = self::MESSAGE, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
