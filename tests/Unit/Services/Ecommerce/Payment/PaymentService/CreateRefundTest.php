<?php

/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Tests\Unit\Services\Ecommerce\Payment\PaymentService;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Models\Ecommerce\Refund;
use App\Services\Ecommerce\Payment\PaymentService;
use Brick\Money\Money;

/**
 * @covers \App\Services\Ecommerce\Payment\PaymentService::createRefund
 */
class CreateRefundTest extends PaymentServiceTestCase
{
    /**
     * Test that SUT will return a well structure array
     */
    public function testItWillCreateRefundAndLog(): void
    {
        $uniqueFaker = $this->faker->unique(true);

        $reason = $this->faker->randomElement(Refund::REASONS);
        $amountToRefund = Money::of(200, 'USD');
        $parts = [];

        /** @var CompletedOrder $order */
        $order = factory(CompletedOrder::class)->make([
            'id' => $uniqueFaker->numberBetween(1, 100),
            'total_amount' => $this->faker->numberBetween(100, 1000),
            'parts' => []
        ]);

        /** @var Refund $expectedRefund */
        $expectedRefund = factory(Refund::class)->make([
            'id' => $uniqueFaker->numberBetween(1, 100),
            'order_id' => $order->id,
            'dealer_id' => $order->dealer_id,
            'reason' => $reason,
            'parts' => $parts
        ]);

        $dependencies = new PaymentServiceDependencies();

        $dependencies->refundRepository
            ->shouldReceive('create')
            ->andReturn($expectedRefund)
            ->with([
                'order_id' => $order->id,
                'dealer_id' => $order->dealer_id,
                'amount' => $amountToRefund->getAmount(),
                'reason' => $reason,
                'parts' => $parts
            ])
            ->once();

        $dependencies->logger
            ->shouldReceive('info')
            ->once()
            ->with(
                sprintf('A refund process for {%d} order has begun', $order->id),
                ['id' => $order->id, 'amount' => $amountToRefund->getAmount(), 'parts' => $parts]
            );

        $service = $this->getMockBuilder(PaymentService::class)
            ->setConstructorArgs($dependencies->getOrderedArguments())
            ->getMock();

        /** @var Refund $expectedRefund */
        $refund = $this->invokeMethod($service, 'createRefund', [$order, $amountToRefund, $parts, $reason]);

        $this->assertSame($expectedRefund, $refund);
    }
}
