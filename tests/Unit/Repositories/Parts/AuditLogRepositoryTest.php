<?php

namespace Tests\Unit\Repositories\Dms;

use App\Models\Parts\AuditLog;
use App\Repositories\Parts\AuditLogRepositoryInterface;
use App\Repositories\Parts\AuditLogRepository;
use Mockery;
use Tests\TestCase;

/**
 * @coversDefaultClass App\Repositories\Parts\AuditLogRepository
 */
class AuditLogRepositoryTest extends TestCase
{
    
    /**
     * @var LegacyMockInterface|App\Repositories\Parts\AuditLogRepositoryInterface
     */
    private $auditLogRepositoryMock;
    
    /**
     * @var App\Models\Parts\AuditLog
     */
    private $auditLogMock;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->auditLogRepositoryMock = Mockery::mock(AuditLogRepositoryInterface::class);
        $this->app->instance(AuditLogRepositoryInterface::class, $this->auditLogRepositoryMock);
        
        $this->auditLogMock = $this->getEloquentMock(AuditLog::class);
        $this->app->instance(AuditLog::class, $this->auditLogMock);
    }
    
    /**
     * @covers ::getByYear
     *
     * @group DMS
     * @group DMS_PARTS
     */
    public function testGetByYear(): void 
    {
        
    }
    

}
