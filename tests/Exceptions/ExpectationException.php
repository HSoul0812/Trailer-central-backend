<?php

declare(strict_types=1);

namespace Tests\Exceptions;

use Exception;

/**
 * Exception used for failed assertions in tests.
 */
class ExpectationException extends Exception
{
}
