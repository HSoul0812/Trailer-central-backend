<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array
     */
    protected $except = [
        'password',
        'password_confirmation',
    ];

    protected function transform($key, $value)
    {
        if ($value === 'true' || $value === 'TRUE') {
            return true;
        }
        if ($value === 'false' || $value === 'FALSE') {
            return false;
        }
        return $value;
    }
}
