<?php

declare(strict_types=1);

namespace App\Support\Traits;

use App\Exceptions\PropertyDoesNotExist;

trait WithGetter
{
    /**
     * @return mixed
     *
     * @throws PropertyDoesNotExist when the provided property does not exist
     */
    public function __get(string $key)
    {
        if (property_exists($this, $key)) {
            return $this->{$key};
        }

        throw new PropertyDoesNotExist();
    }
}
