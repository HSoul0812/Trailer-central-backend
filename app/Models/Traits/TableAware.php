<?php

namespace App\Models\Traits;

trait TableAware {

    public static function getTableName()
    {
        return with(new static)->getTable();
    }
}
