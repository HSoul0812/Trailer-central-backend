<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;
use DateTimeInterface;

class Types
{
    /**
     * Ensures a numeric value is integer or null
     *
     * @param string|int|float|null $number
     * @return float|int|null
     */
    public static function ensureInt($number)
    {
        if (!is_numeric($number) && is_string($number) && empty($number)) {
            return null;
        }

        if (is_numeric($number) && is_string($number)) {
            return (int) $number;
        }

        if (is_string($number) && !is_numeric($number)) {
            return (int) (preg_replace(['/,/', '/[^0-9.-]/'], ['.', ''], $number));
        }

        return $number;
    }

    /**
     * Ensures a numeric value has a correct primitive type like int, float or null
     *
     * @param string|int|float|null $number
     * @return float|int|null
     */
    public static function ensureNumeric($number)
    {
        if (!is_numeric($number) && is_string($number) && empty($number)) {
            return null;
        }

        if (is_numeric($number) && is_string($number)) {
            return 1 * $number;
        }

        if (is_string($number) && !is_numeric($number)) {
            return (float) $number;
        }

        return $number;
    }

    /**
     * Ensures a value has a correct primitive boolean type or null
     * Sometimes it is:
     *  - An string like "false" or "true"
     *  - A numeric value like 0 or 1
     *  - An empty value
     *
     * @param string|boolean|int|null $value
     * @return boolean|null
     */
    public static function ensureBoolean($value): ?bool
    {
        if (is_null($value) || (is_string($value) && !is_numeric($value) && empty($value))) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool)$value;
        }

        return is_string($value) && strtolower(trim($value)) === 'true';
    }

    /**
     * Mostly to avoid any breaking change by doing a Eloquent casting
     *
     * @param Carbon|DateTimeInterface|string|null $date
     * @return string|null
     */
    public static function ensureDateString($date): ?string
    {
        if ($date instanceof Carbon) {
            return $date->toDateTimeString();
        }

        if ($date instanceof DateTimeInterface) {
            return $date->format('Y-m-d H:i:s');
        }

        if (!empty($date) && is_string($date)) {
            return Carbon::parse($date)->toDateTimeString();
        }

        return null;
    }
}
