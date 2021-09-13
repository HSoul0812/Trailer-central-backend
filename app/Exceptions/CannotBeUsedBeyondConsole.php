<?php

declare(strict_types=1);

namespace App\Exceptions;

use JetBrains\PhpStorm\Pure;
use Throwable;

class CannotBeUsedBeyondConsole extends \BadMethodCallException
{
    #[Pure]
    public function __construct($message = 'Method or class cannot be used beyond the console', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
