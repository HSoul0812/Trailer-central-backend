<?php

declare(strict_types=1);

namespace App\Exceptions\Ecommerce;

class TextrailSyncException extends TextrailException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
