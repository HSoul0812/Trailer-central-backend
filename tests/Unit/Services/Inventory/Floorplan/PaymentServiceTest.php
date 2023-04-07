<?php

namespace Tests\Unit\Services\Inventory\Floorplan;

use App\Repositories\Inventory\Floorplan\PaymentRepositoryInterface;
use App\Services\Inventory\Floorplan\PaymentService;
use App\Models\Inventory\Floorplan\Payment;
use Illuminate\Contracts\Container\BindingResolutionException;
use Mockery;
use Faker\Provider\Uuid;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_INVENTORY
 *
 * Test for App\Services\Inventory\Floorplan\PaymentService
 *
 * Class PaymentServiceTest
 * @package Tests\Unit\Services\Inventory\Floorplan
 *
 * @coversDefaultClass \App\Services\Inventory\Floorplan\PaymentService
 */
class PaymentServiceTest extends TestCase
{
    /**
     * @var LegacyMockInterface|PaymentRepositoryInterface
     */
    private $paymentRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->paymentRepositoryMock = Mockery::mock(PaymentRepositoryInterface::class);
        $this->app->instance(PaymentRepositoryInterface::class, $this->paymentRepositoryMock);
    }

    /**
     * @covers ::createBulk
     *
     * @group DMS
     * @group DMS_INVENTORY_FLOORPLAN
     *
     * @throws BindingResolutionException
     */
    public function testCreateBulk()
    {
        $dealerId = 1001;
        $paymentParams = [
            [
                'inventory_id' => 123,
                'account_id' => 123,
                'amount' => 123,
                'type' => 'balance',
                'payment_type' => 'cash',
            ]
        ];
        $payment = new Payment($paymentParams[0]);
        $payment->id = 123;

        /** @var PaymentService $service */
        $service = $this->app->make(PaymentService::class);

        // Mock Create Catalog Access Token
        $this->paymentRepositoryMock
            ->shouldReceive('createBulk')
            ->once()
            ->andReturn(collect([$payment]));

        $result = $service->createBulk($dealerId, $paymentParams, Uuid::uuid());

        $this->assertSame(count($result), 1);
        $this->assertInstanceOf(Payment::class, $result[0]);
        $this->assertSame($result[0]['id'], $payment->id);
    }
}
