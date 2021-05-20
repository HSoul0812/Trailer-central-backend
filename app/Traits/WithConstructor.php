<?php

declare(strict_types=1);

namespace App\Traits;

use App\Exceptions\PropertyDoesNotExists;

trait WithConstructor
{
    /**
     * @param \stdClass|array $properties
     * @return mixed
     * @throws PropertyDoesNotExists when the desired property does not exists
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $name => $value) {
            // Convert full_name -> fullName
            $property = str_replace('_', '', lcfirst(ucwords($name, '_')));
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
                continue;
            }

            // Convert full-name -> fullName
            $property = str_replace('-', '', lcfirst(ucwords($name, '-')));
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
                continue;
            }

            // Property Does Not Exist!
//            throw new PropertyDoesNotExists("$property is not settable");
        }
    }
}