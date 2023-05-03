<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Integrations\TrailerCentral\Console\AbstractSyncService;

use App\Repositories\Integrations\TrailerCentral\SourceRepositoryInterface;
use App\Repositories\SyncProcessRepositoryInterface;
use App\Services\Integrations\TrailerCentral\Console\LogServiceInterface;
use App\Services\LoggerServiceInterface;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Database\ConnectionInterface;
use JetBrains\PhpStorm\ArrayShape;
use Tests\Common\UnitTestCase;

abstract class SyncServiceTestCase extends UnitTestCase
{
    #[ArrayShape([
        'sourceRepository' => '\App\Repositories\Integrations\TrailerCentral\SourceRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject',
        'targetRepository' => '\App\Services\Integrations\TrailerCentral\Console\LogServiceInterface|\PHPUnit\Framework\MockObject\MockObject',
        'processRepository' => '\App\Repositories\SyncProcessRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject',
        'app' => '\Illuminate\Contracts\Foundation\Application|\PHPUnit\Framework\MockObject\MockObject',
        'logger' => '\App\Services\LoggerServiceInterface|\Mockery\LegacyMockInterface|\PHPUnit\Framework\MockObject\MockObject',
        'connection' => '\Illuminate\Database\ConnectionInterface|\Mockery\LegacyMockInterface|\PHPUnit\Framework\MockObject\MockObject',
    ])]
    protected function mockDependencies(): array
    {
        return [
            'sourceRepository' => $this->mockClassWithoutArguments(SourceRepositoryInterface::class),
            'targetRepository' => $this->mockClassWithoutArguments(LogServiceInterface::class),
            'processRepository' => $this->mockClassWithoutArguments(SyncProcessRepositoryInterface::class),
            'app' => $this->mockClassWithoutArguments(ApplicationContract::class),
            'logger' => $this->mockClassWithoutArguments(LoggerServiceInterface::class),
            'connection' => $this->mockClassWithoutArguments(ConnectionInterface::class),
        ];
    }
}
