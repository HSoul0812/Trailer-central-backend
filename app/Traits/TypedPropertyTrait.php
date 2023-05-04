<?php

namespace App\Traits;

use ReflectionClass;

trait TypedPropertyTrait
{
    use FieldTypeTrait;

    private ?ReflectionClass $_reflectionClass = null;

    public function setTypedProperty(string $field, $value)
    {
        if (!$this->_reflectionClass) {
            $this->_reflectionClass = new ReflectionClass($this);
        }

        if ($value !== null) {
            $fieldType = $this->getType($field);
            if ($fieldType === 'int') {
                $this->$field = intval($value);
            } elseif ($fieldType === 'float') {
                $this->$field = floatval($value);
            } else {
                $this->$field = $value;
            }
        } else {
            $this->$field = null;
        }
    }
}
