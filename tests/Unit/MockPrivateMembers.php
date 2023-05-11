<?php

/** @noinspection PhpMissingParamTypeInspection */
/* @noinspection PhpMissingReturnTypeInspection */

declare(strict_types=1);

namespace Tests\Unit;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use TypeError;

/**
 * Provides capability to call non-public methods of a class and set/get non-public attributes.
 */
trait MockPrivateMembers
{
    /**
     * Call protected/private method of a class.
     *
     * @param object|string $object     Class name, or instantiated object that we will run method on
     * @param string        $methodName method name to call
     * @param array         $parameters array of parameters to pass into method
     *
     * @throws ReflectionException when the class does not exist
     *
     * @return mixed Method return
     */
    public function invokeMethod($object, string $methodName, array $parameters = [])
    {
        $method = (new ReflectionClass($object))->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs(is_string($object) ? null : $object, $parameters);
    }

    /**
     * Sets value for protected/private property of a class.
     *
     * @param object|string $object       Class name, or instantiated object that we will set attribute on
     * @param string        $propertyName Property name to set
     * @param mixed         $value        Property value to set
     *
     * @throws ReflectionException when the class property does not exist
     */
    public function setProperty($object, string $propertyName, $value): void
    {
        $property = $this->makePropertyAccessible($object, $propertyName);
        $property->setValue($object, $value);
        $property->setAccessible(false);
    }

    /**
     * Gets the value for protected/private property of a class.
     *
     * @param object|string $object       Class name, or instantiated object that we will get property from
     * @param string        $propertyName Property name to set
     *
     * @throws TypeError           when the property is non-static, and you provide a class name instead an object
     * @throws ReflectionException when the class property does not exist
     *
     * @return mixed
     */
    public function getProperty($object, string $propertyName)
    {
        return $this->makePropertyAccessible($object, $propertyName)->getValue($object);
    }

    /**
     * Call protected/private property of a class.
     *
     * @param object|string $object       Class name, or instantiated object that we will access the property
     * @param string        $propertyName Name of the property to access
     *
     * @throws ReflectionException when the class/property does not exist
     */
    private function makePropertyAccessible($object, string $propertyName): ReflectionProperty
    {
        $property = new ReflectionProperty($object, $propertyName);
        $property->setAccessible(true);

        return $property;
    }
}
