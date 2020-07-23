<?php

namespace App\Traits\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait CompositePrimaryKeys
 * @package App\Traits\Models
 */
trait CompositePrimaryKeys
{
    /**
     * @param Builder $query
     * @return Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        foreach ($this->getKeyName() as $keyName) {
            $query->where($keyName, '=', $this->original[$keyName]);
        }

        return $query;
    }
}
