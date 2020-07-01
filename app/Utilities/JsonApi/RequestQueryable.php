<?php


namespace App\Utilities\JsonApi;


use Illuminate\Database\Eloquent\Builder;

/**
 * Interface Queryable
 *
 * Implement on repositories that can be made queryable
 *
 * @package App\Utilities\JsonApi
 */
interface RequestQueryable
{
    public function withRequest($request);
    public function withQuery(Builder $query);
}
