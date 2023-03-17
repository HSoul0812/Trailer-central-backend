<?php

namespace App\Domains\Commands\Traits;

trait PrependsTimestamp
{
    protected function getPrependString(): string
    {
        if (property_exists($this, 'outputTimestampFormat')) {
            return now()->format($this->outputTimestampFormat) . ' ';
        }

        return now()->format('[Y-m-d H:i:s]') . ' ';
    }
}
