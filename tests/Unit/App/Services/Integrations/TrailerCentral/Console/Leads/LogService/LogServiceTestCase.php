<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Integrations\TrailerCentral\Console\Leads\LogService;

use Illuminate\Database\ConnectionInterface;
use ReflectionClass;
use ReflectionMethod;
use Tests\Common\UnitTestCase;

abstract class LogServiceTestCase extends UnitTestCase
{
    protected function mockDependency(): \PHPUnit\Framework\MockObject\MockObject|ConnectionInterface
    {
        $interfaceReflection = new ReflectionClass(ConnectionInterface::class);
        $availableMethods = collect($interfaceReflection->getMethods())
            ->map(fn (ReflectionMethod $method) => $method->getName())
            ->toArray();

        return $this->getMockBuilder(ConnectionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPdo'])
            ->onlyMethods($availableMethods)
            ->getMock();
    }
}
