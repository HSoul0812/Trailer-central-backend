<?php


namespace App\Utilities\JsonApi;


/**
 * Interface Filterable
 *
 * Implement on eloquent models to indicate they can be used with JsonApiQueryBuilder's filter
 *
 * @package App\Utilities\JsonApi
 */
interface Filterable
{
    /**
     * Returns a list of columns which the filter operation may be applied
     *
     * Examples:
     * 1. allow filtering on columns name, age, created_at: ['name', 'age', 'created_at']
     * 2. allow filtering on all columns: ['*']
     * 3. do not allow filtering: null
     *
     * @return array|null
     */
    public function jsonApiFilterableColumns(): ?array;
}
