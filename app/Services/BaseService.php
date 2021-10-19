<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseService
 *
 * @package App\Http\Services
 */
abstract class BaseService
{
    /**
     * Get path of particular resource
     */
    abstract public function resource(): string;

    /**
     * Show specific resource
     *
     * @param Model $model
     * @param string|null $resource
     *
     * @return object
     */
    public function show(Model $model, string $resource = null): object
    {
        $resource = $resource ?? $this->resource();

        return new $resource($model);
    }
}
