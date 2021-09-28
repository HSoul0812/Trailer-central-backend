<?php

declare(strict_types=1);

/** @noinspection PhpMissingReturnTypeInspection */

namespace Tests\Common;

use PHPUnit\Framework\TestCase;
use Tests\Unit\MockPrivateMembers;

abstract class UnitTestCase extends TestCase
{
    use MockPrivateMembers;

    public function setUp(): void
    {
        parent::setUp();

        if (method_exists($this, 'setUpFaker')) {
            $this->setUpFaker();
        }
    }

    /**
     * @throws \ReflectionException when the class property does not exist
     *
     * @return mixed|\PHPUnit\Framework\MockObject\MockObject
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function mockEloquent(string $class, array $attributes = [], array $methods = [])
    {
        $mock = $this->createPartialMock($class, $methods);
        $this->setProperty($mock, 'attributes', $attributes);

        return $mock;
    }

    /**
     * @return mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    public function mockClassWithoutArguments(string $class)
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
