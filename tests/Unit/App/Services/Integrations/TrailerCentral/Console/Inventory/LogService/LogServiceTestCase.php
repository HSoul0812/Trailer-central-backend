<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Integrations\TrailerCentral\Console\Inventory\LogService;

use App\Repositories\Inventory\InventoryLogRepositoryInterface;
use Illuminate\Database\ConnectionInterface;
use JetBrains\PhpStorm\ArrayShape;
use ReflectionClass;
use ReflectionMethod;
use Tests\Common\UnitTestCase;

abstract class LogServiceTestCase extends UnitTestCase
{
    #[ArrayShape([
        'repository' => "\App\Repositories\Inventory\InventoryLogRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject",
        'connection' => "\Illuminate\Database\ConnectionInterface|\PHPUnit\Framework\MockObject\MockObject",
    ])]
    protected function mockDependencies(): array
    {
        $interfaceReflection = new ReflectionClass(ConnectionInterface::class);
        $availableMethods = collect($interfaceReflection->getMethods())
            ->map(fn (ReflectionMethod $method) => $method->getName())
            ->toArray();

        return [
            'repository' => $this->mockClassWithoutArguments(InventoryLogRepositoryInterface::class),
            'connection' => $this->getMockBuilder(ConnectionInterface::class)
                ->disableOriginalConstructor()
                ->addMethods(['getPdo'])
                ->onlyMethods($availableMethods)
                ->getMock(),
        ];
    }
}
