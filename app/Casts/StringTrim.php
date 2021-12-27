<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;
use Illuminate\Database\Eloquent\Model;

class StringTrim implements CastsInboundAttributes
{
    /**
     * Prepare the given value for storage.
     *
     * @param Model $model
     * @param string $key
     * @param string $value
     * @param array $attributes
     *
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        if ($key === 'display_name') {
            dd($value);
        }
        $text = trim($value);
        return !empty($text)
            ? self::trimWhiteSpaces($text)
            : '';
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public static function trimWhiteSpaces(string $text): string
    {
        return preg_replace('/\s+/', ' ', $text);
    }
}
