<?php

namespace Tests\Unit\Repositories\Dms;

use App\Models\Parts\AuditLog;
use App\Repositories\Parts\AuditLogRepositoryInterface;
use App\Repositories\Parts\AuditLogRepository;
use Mockery;
use Tests\TestCase;

/**
 * @coversDefaultClass App\Repositories\Dms\ServiceOrderRepository
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
     */
    public function testGetByYear(): void 
    {
        $serviceRepoParams = [
            'id' =>  $this->serviceOrderMock->id,
            'status' => ServiceOrder::SERVICE_ORDER_STATUS['picked_up']
        ];
                
        $this->serviceOrderMock
                ->shouldReceive('save')
                ->once()
                ->andReturn(new ServiceOrder([
                    'id' => $this->serviceOrderMock->id,
                    'status' => ServiceOrder::SERVICE_ORDER_STATUS['ready_for_pickup']
                ]));
        
        $this->serviceOrderMock
                ->shouldReceive('fill')
                ->once()
                ->with($serviceRepoParams);
        
        $this->serviceOrderMock
                ->shouldReceive('findOrFail')
                ->once()
                ->with($this->serviceOrderMock->id)
                ->andReturn($this->serviceOrderMock);        
        

        $serviceRepo = $this->app->make(ServiceOrderRepository::class);

        $result = $serviceRepo->update($serviceRepoParams);
        $this->assertEquals($result->id, $this->serviceOrderMock->id);
    }
    
    /**
     * @covers ::get
     */
    public function testGetServiceOrder(): void 
    {
        $serviceRepoParams = [
            'id' =>  $this->serviceOrderMock->id,
            'status' => ServiceOrder::SERVICE_ORDER_STATUS['picked_up']
        ];                
        
        $this->serviceOrderMock
                ->shouldReceive('findOrFail')
                ->once()
                ->with($this->serviceOrderMock->id)
                ->andReturn($this->serviceOrderMock);        

        $serviceRepo = $this->app->make(ServiceOrderRepository::class);

        $result = $serviceRepo->get($serviceRepoParams);
        $this->assertEquals($result->id, $this->serviceOrderMock->id);
    }

}
