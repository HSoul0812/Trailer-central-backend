<?php

namespace App\Models\Traits\Inventory;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait CompositePrimaryKeys
 * @package App\Models\Traits\Inventory
 */
trait CompositePrimaryKeys
{
    /**
     * {@inheritDoc}
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        foreach ($this->getKeyName() as $keyName) {
            $query->where($keyName, '=', $this->original[$keyName]);
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function getIncrementing(): bool
    {
        return false;
    }
}
