<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Collection;
use InvalidArgumentException;

class CriteriaBuilder extends Collection
{
    /**
     * @return mixed
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function getOrFail(string $key)
    {
        $value = $this->get($key);

        if (blank($value)) {
            throw new InvalidArgumentException("'$key' is required.");
        }

        return $value;
    }

    public function isNotBlank(string $key): bool
    {
        return $this->has($key) && !blank($this->get($key));
    }

    public function addCriteria($key, $value): static
    {
        $this->offsetSet($key, $value);

        return $this;
    }
}
