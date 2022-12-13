<?php

namespace App\Console\Traits;

/**
 * Inspiration from https://stackoverflow.com/a/33050165/17434423
 */
trait PrependsOutput
{
    public function line($string, $style = null, $verbosity = null)
    {
        parent::line($this->prepend($string), $style, $verbosity);
    }

    protected function prepend($string): string
    {
        if (method_exists($this, 'getPrependString')) {
            return $this->getPrependString() . $string;
        }

        return $string;
    }
}
