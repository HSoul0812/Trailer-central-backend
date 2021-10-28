<?php

/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Tests\Unit\Services\Ecommerce\Payment\PaymentService;

use App\Exceptions\Ecommerce\RefundAmountException;
use App\Exceptions\Ecommerce\RefundException;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Models\Parts\Textrail\Part;
use App\Services\Ecommerce\Payment\PaymentService;
use Brick\Money\Money;

/**
 * @covers \App\Services\Ecommerce\Payment\PaymentService::ensureOrderCanBeRefunded
 */
class EnsureOrderCanBeRefundedTest extends PaymentServiceTestCase
{
    /**
     * @dataProvider ordersNotRefundableProvider
     *
     * @param  array  $orderAttributes
     * @param  float  $amount
     * @param  array  $parts
     * @param  string  $expectedException
     * @param  string  $expectedExceptionMessage
     */
    public function testItWillThrowAnExceptionDueCommonReasons(
        array $orderAttributes,
        float $amount,
        array $parts,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        $amountToRefund = Money::of($amount, 'USD');
        $order = factory(CompletedOrder::class)->make(array_merge($orderAttributes, ['parts' => []]));

        $dependencies = new PaymentServiceDependencies();

        $service = $this->getMockBuilder(PaymentService::class)
            ->setConstructorArgs($dependencies->getOrderedArguments())
            ->getMock();

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->invokeMethod($service, 'ensureOrderCanBeRefunded', [$order, $amountToRefund, $parts]);
    }

    /**
     * Test SUT will throw and exception when the provided amount is greater than remaining balance
     */
    public function testItWillThrowAnExceptionDueBalance(): void
    {
        $amountToRefund = Money::of(200, 'USD');

        /** @var CompletedOrder $order */
        $order = factory(CompletedOrder::class)->make([
            'id' => $this->faker->unique(true)->numberBetween(1, 100),
            'total_amount' => $this->faker->numberBetween(100, 1000),
            'payment_status' => CompletedOrder::PAYMENT_STATUS_PAID,
            'refund_status' => CompletedOrder::REFUND_STATUS_UNREFUNDED,
            'payment_intent' => $this->faker->uuid,
            'parts' => []
        ]);

        $dependencies = new PaymentServiceDependencies();

        $dependencies->refundRepository
            ->shouldReceive('getRefundedAmount')
            ->andReturn(Money::of($order->total_amount - 1, 'USD'))
            ->with($order->id)
            ->once();

        $service = $this->getMockBuilder(PaymentService::class)
            ->setConstructorArgs($dependencies->getOrderedArguments())
            ->getMock();

        $this->expectException(RefundAmountException::class);
        $this->expectExceptionMessage(
            sprintf('%d order is not refundable due the amount is greater than its balance', $order->id)
        );

        $this->invokeMethod($service, 'ensureOrderCanBeRefunded', [$order, $amountToRefund, []]);
    }

    /**
     * Test SUT will throw and exception when some of provided parts aren't match with any order part
     */
    public function testItWillThrowAnExceptionDuePartsDontMatch(): void
    {
        $amountToRefund = Money::of(200, 'USD');
        $partIdToRefund = 8;

        /** @var CompletedOrder $order */
        $order = factory(CompletedOrder::class)->make([
            'id' => $this->faker->unique(true)->numberBetween(1, 100),
            'total_amount' => $this->faker->numberBetween(200, 1000),
            'payment_status' => CompletedOrder::PAYMENT_STATUS_PAID,
            'refund_status' => CompletedOrder::REFUND_STATUS_UNREFUNDED,
            'payment_intent' => $this->faker->uuid,
            'parts' => [['id' => 4, 'qty' => 3], ['id' => 6, 'qty' => 3]]
        ]);

        $dependencies = new PaymentServiceDependencies();

        $dependencies->refundRepository
            ->shouldReceive('getRefundedAmount')
            ->andReturn(Money::zero('USD'))
            ->with($order->id)
            ->once();

        $service = $this->getMockBuilder(PaymentService::class)
            ->setConstructorArgs($dependencies->getOrderedArguments())
            ->getMock();

        $this->expectException(RefundException::class);
        $this->expectExceptionMessage(
            sprintf(
                '%d order cannot be refunded due the provided part %d is not a placed part',
                $order->id,
                $partIdToRefund
            )
        );

        $this->invokeMethod($service, 'ensureOrderCanBeRefunded', [$order, $amountToRefund, [$partIdToRefund]]);
    }

    /**
     * @return array[]
     */
    public function ordersNotRefundableProvider(): array
    {   // [array $orderAttributes, float $amount, array $parts, string $expectedException, string $expectedExceptionMessage]
        return [
            'is unpaid' => [
                [
                    'id' => 2,
                    'payment_status' => CompletedOrder::PAYMENT_STATUS_UNPAID,
                    'refunded_status' => CompletedOrder::REFUND_STATUS_UNREFUNDED
                ],
                300,
                [],
                RefundAmountException::class,
                '2 order is not refundable due it is unpaid'
            ],
            'is refunded' => [
                [
                    'id' => 2,
                    'payment_status' => CompletedOrder::PAYMENT_STATUS_PAID,
                    'refund_status' => CompletedOrder::REFUND_STATUS_REFUNDED
                ],
                300,
                [],
                RefundAmountException::class, '2 order is not refundable due it is refunded'
            ],
            'has not a payment unique id' => [
                [
                    'id' => 2,
                    'payment_status' => CompletedOrder::PAYMENT_STATUS_PAID,
                    'refund_status' => CompletedOrder::REFUND_STATUS_UNREFUNDED
                ],
                300,
                [],
                RefundException::class, '2 order is not refundable due it has not a payment unique id'
            ]
        ];
    }
}
