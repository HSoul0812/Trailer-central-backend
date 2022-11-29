<?php

namespace App\Console\Traits;

/**
 * Inspiration from https://stackoverflow.com/a/33050165/17434423
 */
trait PrependsTimestamp
{
    protected function getPrependString(): string
    {
        $format = property_exists($this, 'outputTimestampFormat')
            ? $this->outputTimestampFormat
            : '[Y-m-d H:i:s]';

        return now()->format($format) . ' ';
    }
}
