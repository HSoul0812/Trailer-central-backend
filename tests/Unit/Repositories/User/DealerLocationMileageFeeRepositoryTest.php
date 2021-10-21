<?php


namespace Unit\Repositories\User;


use App\Models\User\DealerLocationMileageFee;
use App\Repositories\User\DealerLocationMileageFeeRepositoryInterface;
use Mockery;
use Tests\TestCase;

/**
 * Class DealerLocationMileageFeeRepositoryTest
 * @coversDefaultClass  App\Repositories\User\DealerLocationMileageFee
 * @package Unit\Repositories\User
 */
class DealerLocationMileageFeeRepositoryTest extends TestCase
{
    /**
     * @var array|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $dealerLocationMileageFeeMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->dealerLocationMileageFeeMock = $this->getEloquentMock(DealerLocationMileageFee::class);
        $this->app->instance(DealerLocationMileageFee::class, $this->dealerLocationMileageFeeMock);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @covers ::create
     */
    public function testCreate() {
        $params = [
            'dealer_location_id' => 1,
            'inventory_category_id' => 1
        ];

        $this->dealerLocationMileageFeeMock
            ->shouldReceive('create')
            ->once()
            ->andReturn(new DealerLocationMileageFee($params));
        $model = $this->getConcreteRepository()->create($params);
        $this->assertEquals($model->dealer_location_id, $params['dealer_location_id']);
        $this->assertEquals($model->inventory_category_id, $params['inventory_category_id']);
    }

    /**
     * @covers ::update
     */
    public function testUpdate() {
        $params = [
            'id' => 1,
            'dealer_location_id' => 1,
            'inventory_category_id' => 1,
        ];
        $this->dealerLocationMileageFeeMock->dealer_location_id = $params['dealer_location_id'];
        $this->dealerLocationMileageFeeMock->inventory_category_id = $params['inventory_category_id'];

        $this->dealerLocationMileageFeeMock
            ->shouldReceive('findOrFail')
            ->once()
            ->andReturnSelf();
        $this->dealerLocationMileageFeeMock
            ->shouldReceive('fill')
            ->once()
            ->andReturnSelf();
        $this->dealerLocationMileageFeeMock
            ->shouldReceive('save')
            ->once()
            ->andReturnSelf();
        $update = $this->getConcreteRepository()->update($params);

        $this->assertEquals($update->dealer_location_id, $params['dealer_location_id']);
        $this->assertEquals($update->inventory_category_id, $params['inventory_category_id']);
    }

    /**
     * @covers ::delete
     */
    public function testDelete() {
        $params = ['id' => 1];
        $this->dealerLocationMileageFeeMock
            ->shouldReceive('where')
            ->once()
            ->andReturnSelf();
        $this->dealerLocationMileageFeeMock
            ->shouldReceive('delete')
            ->once()
            ->andReturn(true);
        $this->getConcreteRepository()->delete($params);
    }

    /**
     * @covers ::get
     */
    public function testGet() {
        $params = [
            'inventory_category_id' => 1,
            'dealer_location_id' => 1
        ];
        $this->dealerLocationMileageFeeMock->inventory_category_id = $params['inventory_category_id'];
        $this->dealerLocationMileageFeeMock->dealer_location_id = $params['dealer_location_id'];

        $this->dealerLocationMileageFeeMock
            ->shouldReceive('where')
            ->once()
            ->andReturnSelf();
        $this->dealerLocationMileageFeeMock
            ->shouldReceive('where')
            ->once()
            ->andReturnSelf();
        $this->dealerLocationMileageFeeMock
            ->shouldReceive('firstOrFail')
            ->andReturnSelf();
        $model = $this->getConcreteRepository()->get($params);

        $this->assertEquals($model->inventory_category_id, $params['inventory_category_id']);
        $this->assertEquals($model->dealer_location_id, $params['dealer_location_id']);
    }

    public function getConcreteRepository() {
        return app()->make(DealerLocationMileageFeeRepositoryInterface::class);
    }
}
