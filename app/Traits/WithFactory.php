<?php

declare(strict_types=1);

namespace App\Traits;

use InvalidArgumentException;
use stdClass;

trait WithFactory
{
    /**
     * @param stdClass|array $properties
     * @return static
     */
    public static function from($properties): self
    {
        $object = new static();

        foreach ((array) $properties as $property => $value) {

            $methodName = 'set' . str_replace('_', '', ucwords($property, '_'));

            if (method_exists($object, $methodName)) {
                $object->$methodName($value);
            } else {
                if (!property_exists($object, $property)) {
                    throw new InvalidArgumentException("$property is not settable");
                }

                $object->$property = $value;
            }
        }

        return $object;
    }
}
