<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

trait DealerLocationRequestTrait
{
    public function getInclude(): string
    {
        return (string)$this->input('include');
    }

    public function getId(): int
    {
        return (int)$this->input('id');
    }

    public function getDealerId(): int
    {
        return (int)$this->input('dealer_id');
    }

    /**
     * Retrieve an input item from the request.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    abstract public function input($key = null, $default = null);
}
