<?php

declare(strict_types=1);

namespace App\Support\Traits;

trait TableAware
{
    private static self $static;

    public static function getTableName(): string
    {
        static::$static = static::$static ?? with(new static());

        return static::$static->getTable();
    }
}
