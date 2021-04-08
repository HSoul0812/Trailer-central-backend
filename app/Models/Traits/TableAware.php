<?php

namespace App\Models\Traits;

/**
 * Trait TableAware
 * @package App\Models\Traits
 */
trait TableAware {
    public static function getTableName()
    {
        return with(new static)->getTable();
    }
}
