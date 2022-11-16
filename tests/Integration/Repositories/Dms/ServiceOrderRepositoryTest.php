<?php

namespace Tests\Integration\Repositories\Dms;

use App\Models\Inventory\Category;
use App\Repositories\Dms\ServiceOrderRepositoryInterface;
use App\Repositories\Dms\ServiceOrderRepository;
use App\Models\CRM\Dms\ServiceOrder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\database\seeds\Dms\ServiceOrderSeeder;
use Tests\TestCase;

/**
 * @coversDefaultClass App\Repositories\Dms\ServiceOrderRepository
 */
class ServiceOrderRepositoryTest extends TestCase
{    
    /**
     * @var ServiceOrderSeeder
     */
    private $seeder;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new ServiceOrderSeeder();
    }
    
    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }
    
    /**
     * Test that SUT is properly bound by the application
     *
     * @group DMS
     * @group DMS_SERVICE_ORDER
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     */
    public function testIoCForServiceOrderRepositoryIsWorking(): void
    {
        $this->seeder->seed();

        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(ServiceOrderRepository::class, $concreteRepository);
    } 
    
    /**
     * @covers ::update
     *
     * @group DMS
     * @group DMS_SERVICE_ORDER
     */
    public function testUpdateServiceOrderStatus(): void 
    {
        $this->seeder->seed();
        $serviceOrder = ServiceOrder::where('dealer_id', $this->seeder->getDealerId())->first();
        $concreteRepository = $this->getConcreteRepository();
        
        $updatedServiceOrder = $concreteRepository->update([
            'id' => $serviceOrder->id,
            'status' => ServiceOrder::SERVICE_ORDER_STATUS['picked_up']
        ]);        
        
        $this->assertEquals(ServiceOrder::SERVICE_ORDER_STATUS['picked_up'], $updatedServiceOrder->status);
    }
    
    /**
     * @return ServiceOrderRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): ServiceOrderRepositoryInterface
    {
        return $this->app->make(ServiceOrderRepositoryInterface::class);
    }
}
