<?php

namespace Tests\Unit\Repositories\Dms;

use App\Repositories\Parts\AuditLogRepositoryInterface;
use App\Repositories\Parts\AuditLogRepository;
use Mockery;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;

/**
 * @coversDefaultClass App\Repositories\Parts\AuditLogRepository
 */
class AuditLogRepositoryTest extends TestCase
{
    /**
     * @var LegacyMockInterface|App\Repositories\Parts\AuditLogRepositoryInterface
     */
    private $auditLogRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->auditLogRepositoryMock = Mockery::mock(AuditLogRepositoryInterface::class);
        $this->app->instance(AuditLogRepositoryInterface::class, $this->auditLogRepositoryMock);
    }

    /**
     * @covers ::getByYear
     *
     * @group DMS
     * @group DMS_PARTS
     */
    public function testGetByYear(): void
    {
        $this->auditLogRepositoryMock
            ->shouldReceive('getByYear')
            ->withArgs([2021, 999])->andReturn(resolve(Builder::class));

        $auditLogRepository = resolve(AuditLogRepository::class);

        $response = $auditLogRepository->getByYear(2021, 999);

        $this->assertInstanceOf(Builder::class, $response);
    }


}
