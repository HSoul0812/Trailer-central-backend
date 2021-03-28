<?php

declare(strict_types=1);

namespace App\Traits;

use App\Exceptions\PropertyDoesNotExists;

trait WithConstructor
{
    /**
     * @param stdClass|array $properties
     * @return mixed
     * @throws PropertyDoesNotExists when the desired property does not exists
     */
    public function __construct($properties)
    {
        foreach ($properties as $property => $value) {
            if (!property_exists($this, $property)) {
                throw new InvalidArgumentException("$property is not settable");
            }

            $this->{$property} = $value;
        }
    }
}