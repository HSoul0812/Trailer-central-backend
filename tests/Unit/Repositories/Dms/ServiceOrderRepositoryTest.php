<?php

namespace Tests\Unit\Repositories\Dms;

use App\Models\Inventory\Category;
use App\Repositories\Dms\ServiceOrderRepositoryInterface;
use App\Repositories\Dms\ServiceOrderRepository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\CRM\Dms\ServiceOrder;
use Mockery;
use Tests\TestCase;

/**
 * @coversDefaultClass App\Repositories\Dms\ServiceOrderRepository
 */
class ServiceOrderRepositoryTest extends TestCase
{
    
    /**
     * @var LegacyMockInterface|ServiceOrderRepositoryInterface
     */
    private $serviceOrderRepositoryMock;
    
    /**
     * @var App\Models\CRM\Dms\ServiceOrder
     */
    private $serviceOrderMock;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->serviceOrderRepositoryMock = Mockery::mock(ServiceOrderRepositoryInterface::class);
        $this->app->instance(ServiceOrderRepositoryInterface::class, $this->serviceOrderRepositoryMock);
        
        $this->serviceOrderMock = $this->getEloquentMock(ServiceOrder::class);
        $this->app->instance(ServiceOrder::class, $this->serviceOrderMock);
        $this->serviceOrderMock->id = 1;
        $this->serviceOrderMock->status = ServiceOrder::SERVICE_ORDER_STATUS['ready_for_pickup'];
    }
    
    /**
     * @covers ::update
     *
     * @group DMS
     * @group DMS_SERVICE_ORDER
     */
    public function testUpdateServiceOrderStatus(): void 
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
     *
     * @group DMS
     * @group DMS_SERVICE_ORDER
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
