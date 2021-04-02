<?php

declare(strict_types=1);

namespace App\Traits;

use InvalidArgumentException;

trait WithConstructor
{
    /**
     * @param stdClass|array $properties
     * @return mixed
     * @throws PropertyDoesNotExists when the desired property does not exists
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $property => $value) {
            if (!property_exists($this, $property)) {
                throw new InvalidArgumentException("$property is not settable");
            }

            $this->{$property} = $value;
        }
    }

    /**
     * Convert to CamelCase Then Get
     * 
     * @param array $properties
     */
    public static function getViaCC(array $properties): self {
        // New Array
        $newProperties = [];
        foreach ($properties as $property => $value) {
            // Convert to CamelCase
            $str = str_replace(' ', '', ucwords(str_replace('_', ' ', str_replace('-', ' ', $property))));
            $converted = lcfirst($str);
            $newProperties[$converted] = $value;
        }

        // Return Array
        return new self($newProperties);
    }
}