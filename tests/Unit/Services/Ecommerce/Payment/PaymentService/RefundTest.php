<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Tests\Unit\Services\Ecommerce\Payment\PaymentService;

use App\Exceptions\Ecommerce\AfterRemoteRefundException;
use App\Exceptions\Ecommerce\RefundPaymentGatewayException;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Models\Ecommerce\Refund;
use App\Models\Parts\Textrail\Part;
use App\Services\Ecommerce\Payment\Gateways\Stripe\StripeRefundResult;
use App\Services\Ecommerce\Payment\PaymentService;
use Brick\Money\Exception\MoneyMismatchException;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

/**
 * @covers \App\Services\Ecommerce\Payment\PaymentService::refund
 */
class RefundTest extends PaymentServiceTestCase
{
    public function testItWillThrowAnExceptionDueNonexistentOrder(): void
    {
        $nonExistentOrderId = $this->faker->numberBetween(1, 10000);
        $amountToRefund = Money::of(200, 'USD');

        $expectedException = new ModelNotFoundException();

        $dependencies = new PaymentServiceDependencies();

        $dependencies->orderRepository
            ->shouldReceive('get')
            ->with(['id' => $nonExistentOrderId])
            ->once()
            ->andThrow($expectedException);

        $service = $this->getMockBuilder(PaymentService::class)
            ->setConstructorArgs($dependencies->getOrderedArguments())
            ->addMethods([]) // no mocked methods
            ->getMock();

        $this->expectException(ModelNotFoundException::class);

        $service->refund($nonExistentOrderId, $amountToRefund, []);
    }

    /**
     * Test that SUT will create the refund on the database, but it will throw an exception on the gateway side e.g:
     * a biz validation rule which wasn't met
     */
    public function testItWillCreateRefundButWillFailOnGatewaySide(): void
    {
        $uniqueFaker = $this->faker->unique(true);

        $partIdGenerator = static function () use ($uniqueFaker): int {
            return $uniqueFaker->numberBetween(1, 1000);
        };

        $reason = null;

        $amountToRefund = Money::of(200, 'USD');
        $refundedParts = new Collection();

        /** @var Collection|array<Part> $orderParts */
        $orderParts = factory(Part::class, 3)->make(['id' => $partIdGenerator]);
        /** @var  Part $partToRefund */
        $partToRefund = $orderParts->first();
        $indexedPartToRefund = [
            $partToRefund->id => ['id' => $partToRefund->id, 'amount' => $amountToRefund->getAmount()->toFloat()]
        ];

        /** @var CompletedOrder $order */
        $order = factory(CompletedOrder::class)->make([
            'id' => $this->faker->unique(true)->numberBetween(1, 100),
            'total_amount' => $this->faker->numberBetween(100, 1000),
            'payment_status' => CompletedOrder::PAYMENT_STATUS_PAID,
            'refund_status' => CompletedOrder::REFUND_STATUS_UNREFUNDED,
            'payment_intent' => $this->faker->uuid,
            'parts' => $orderParts->map(function (Part $part): array {
                return ['id' => $part->id, 'qty' => 3, 'price' => $part->price];
            })->toArray()
        ]);

        $expectedException = new RefundPaymentGatewayException('Payment cannot be refunded due it is refunded');

        /** @var Refund $expectedRefund */
        $expectedRefund = factory(Refund::class)->make([
            'id' => $uniqueFaker->numberBetween(1, 100),
            'order_id' => $order->id,
            'dealer_id' => $order->dealer_id,
            'reason' => $reason,
            'parts' => $indexedPartToRefund
        ]);

        $dependencies = new PaymentServiceDependencies();

        $dependencies->orderRepository
            ->shouldReceive('get')
            ->andReturn($order)
            ->with(['id' => $order->id])
            ->once();

        $dependencies->refundRepository
            ->shouldReceive('getRefundedAmount')
            ->andReturn(Money::zero('USD'))
            ->with($order->id)
            ->once();

        $dependencies->refundRepository
            ->shouldReceive('getRefundedParts')
            ->andReturn($refundedParts)
            ->with($order->id)
            ->once();

        $dependencies->refundRepository
            ->shouldReceive('getPartsToBeRefunded')
            ->andReturn(new Collection([$partToRefund]))
            ->with(array_keys($indexedPartToRefund))
            ->once();

        $dependencies->refundRepository
            ->shouldReceive('create')
            ->andReturn($expectedRefund)
            ->with([
                'order_id' => $order->id,
                'dealer_id' => $order->dealer_id,
                'amount' => $amountToRefund->getAmount(),
                'reason' => $reason,
                'parts' => collect($indexedPartToRefund)->values()->toArray()
            ])
            ->once();

        $dependencies->logger
            ->shouldReceive('info')
            ->once()
            ->with(
                sprintf('A refund process for {%d} order has begun', $order->id),
                ['id' => $order->id, 'amount' => $amountToRefund->getAmount(), 'parts' => collect($indexedPartToRefund)->values()->toArray()]
            );

        $dependencies->paymentGatewayService
            ->shouldReceive('refund')
            ->andThrow($expectedException)
            ->with(
                $order->payment_intent,
                $amountToRefund,
                [['sku' => $partToRefund->sku, 'title' => $partToRefund->title, 'id' => $partToRefund->id, 'amount' => $amountToRefund->getAmount()->toFloat()]],
                $reason
            )
            ->once();

        $dependencies->logger
            ->shouldReceive('error')
            ->once();

        $dependencies->refundRepository
            ->shouldReceive('markAsFailed')
            ->with($expectedRefund, $expectedException->getMessage())
            ->once();

        $service = $this->getMockBuilder(PaymentService::class)
            ->setConstructorArgs($dependencies->getOrderedArguments())
            ->addMethods([]) // no mocked methods
            ->getMock();

        $this->expectException(RefundPaymentGatewayException::class);
        $this->expectExceptionMessage($expectedException->getMessage());

        $service->refund($order->id, $amountToRefund, $indexedPartToRefund);
    }

    /**
     * Test that SUT will create the refund on the database, but it will throw an exception due an error after the gateway
     * has done successfully its process, this is a critical error that should be traced somewhere, currently it is saving
     * the gateway response on a log record, also on the same DB refund record
     */
    public function testItWillCreateRefundButWillFailAfterGatewaySuccess(): void
    {
        $uniqueFaker = $this->faker->unique(true);

        $partIdGenerator = static function () use ($uniqueFaker): int {
            return $uniqueFaker->numberBetween(1, 1000);
        };

        $reason = null;

        $amountToRefund = Money::of(200, 'USD');
        $refundedParts = new Collection();

        /** @var Collection|array<Part> $orderParts */
        $orderParts = factory(Part::class, 3)->make(['id' => $partIdGenerator]);
        /** @var  Part $partToRefund */
        $partToRefund = $orderParts->first();
        $indexedPartToRefund = [
            $partToRefund->id => ['id' => $partToRefund->id, 'amount' => $amountToRefund->getAmount()->toFloat()]
        ];

        /** @var CompletedOrder $order */
        $order = factory(CompletedOrder::class)->make([
            'id' => $this->faker->unique(true)->numberBetween(1, 100),
            'total_amount' => $this->faker->numberBetween(100, 1000),
            'payment_status' => CompletedOrder::PAYMENT_STATUS_PAID,
            'refund_status' => CompletedOrder::REFUND_STATUS_UNREFUNDED,
            'payment_intent' => $this->faker->uuid,
            'parts' => $orderParts->map(function (Part $part): array {
                return ['id' => $part->id, 'qty' => 3, 'price' => $part->price];
            })->toArray()
        ]);

        $expectedException = new \Doctrine\DBAL\Exception\InvalidArgumentException(
            "Something goes wrong after gateways remote process has successfully done"
        );

        $expectedGatewayRefund = StripeRefundResult::from(['id' => $this->faker->uuid]);

        /** @var Refund $expectedRefund */
        $expectedRefund = factory(Refund::class)->make([
            'id' => $uniqueFaker->numberBetween(1, 100),
            'order_id' => $order->id,
            'dealer_id' => $order->dealer_id,
            'reason' => $reason,
            'parts' => $indexedPartToRefund
        ]);

        $dependencies = new PaymentServiceDependencies();

        $dependencies->orderRepository
            ->shouldReceive('get')
            ->andReturn($order)
            ->with(['id' => $order->id])
            ->once();

        $dependencies->refundRepository
            ->shouldReceive('getRefundedAmount')
            ->andReturn(Money::zero('USD'))
            ->with($order->id)
            ->once();

        $dependencies->refundRepository
            ->shouldReceive('getRefundedParts')
            ->andReturn($refundedParts)
            ->with($order->id)
            ->once();

        $dependencies->refundRepository
            ->shouldReceive('getPartsToBeRefunded')
            ->andReturn(new Collection([$partToRefund]))
            ->with(array_keys($indexedPartToRefund))
            ->once();

        $dependencies->refundRepository
            ->shouldReceive('create')
            ->andReturn($expectedRefund)
            ->with([
                'order_id' => $order->id,
                'dealer_id' => $order->dealer_id,
                'amount' => $amountToRefund->getAmount(),
                'reason' => $reason,
                'parts' => collect($indexedPartToRefund)->values()->toArray()
            ])
            ->once();

        $dependencies->logger
            ->shouldReceive('info')
            ->once()
            ->with(
                sprintf('A refund process for {%d} order has begun', $order->id),
                ['id' => $order->id, 'amount' => $amountToRefund->getAmount(), 'parts' => $indexedPartToRefund, 'parts' => collect($indexedPartToRefund)->values()->toArray()]
            );

        $dependencies->paymentGatewayService
            ->shouldReceive('refund')
            ->andReturn($expectedGatewayRefund)
            ->with(
                $order->payment_intent,
                $amountToRefund,
                [['sku' => $partToRefund->sku, 'title' => $partToRefund->title, 'id' => $partToRefund->id, 'amount' => $amountToRefund->getAmount()->toFloat()]],
                $reason
            )
            ->once();

        $dependencies->orderRepository
            ->shouldReceive('beginTransaction')
            ->once();

        $dependencies->refundRepository
            ->shouldReceive('markAsFinished')
            ->andThrow($expectedException)
            ->with($expectedRefund, $expectedGatewayRefund)
            ->once();

        $dependencies->orderRepository
            ->shouldReceive('rollbackTransaction')
            ->once();

        $dependencies->logger
            ->shouldReceive('critical')
            ->once();

        $dependencies->refundRepository
            ->shouldReceive('markAsRecoverableFailure')
            ->with($expectedRefund, $expectedGatewayRefund)
            ->once();

        $service = $this->getMockBuilder(PaymentService::class)
            ->setConstructorArgs($dependencies->getOrderedArguments())
            ->addMethods([]) // no mocked methods
            ->getMock();

        $this->expectException(AfterRemoteRefundException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The refund {%d} for {%d} order had a critical error after its remote process: %s',
                $expectedRefund->id,
                $expectedRefund->order_id,
                $expectedException->getMessage()
            )
        );

        $service->refund($order->id, $amountToRefund, $indexedPartToRefund);
    }

    public function testItWillCreateRefundButWillFailJustBeforeGatewaySide(): void
    {
        $uniqueFaker = $this->faker->unique(true);

        $partIdGenerator = static function () use ($uniqueFaker): int {
            return $uniqueFaker->numberBetween(1, 1000);
        };

        $reason = null;

        $amountToRefund = Money::of(200, 'USD');
        $refundedParts = new Collection();

        /** @var Collection|array<Part> $orderParts */
        $orderParts = factory(Part::class, 3)->make(['id' => $partIdGenerator]);
        /** @var  Part $partToRefund */
        $partToRefund = $orderParts->first();
        $indexedPartToRefund = [
            $partToRefund->id => ['id' => $partToRefund->id, 'amount' => $amountToRefund->getAmount()->toFloat()]
        ];

        /** @var CompletedOrder $order */
        $order = factory(CompletedOrder::class)->make([
            'id' => $this->faker->unique(true)->numberBetween(1, 100),
            'total_amount' => $this->faker->numberBetween(100, 1000),
            'payment_status' => CompletedOrder::PAYMENT_STATUS_PAID,
            'refund_status' => CompletedOrder::REFUND_STATUS_UNREFUNDED,
            'payment_intent' => $this->faker->uuid,
            'parts' => $orderParts->map(function (Part $part): array {
                return ['id' => $part->id, 'qty' => 3, 'price' => $part->price];
            })->toArray()
        ]);

        $expectedException = new MoneyMismatchException(
            "Something goes wrong just before call the gateways remote process"
        );

        $expectedGatewayRefund = StripeRefundResult::from(['id' => $this->faker->uuid]);

        /** @var Refund $expectedRefund */
        $expectedRefund = factory(Refund::class)->make([
            'id' => $uniqueFaker->numberBetween(1, 100),
            'order_id' => $order->id,
            'dealer_id' => $order->dealer_id,
            'reason' => $reason,
            'parts' => $indexedPartToRefund
        ]);

        $dependencies = new PaymentServiceDependencies();

        $dependencies->orderRepository
            ->shouldReceive('get')
            ->andReturn($order)
            ->with(['id' => $order->id])
            ->once();

        $dependencies->refundRepository
            ->shouldReceive('getRefundedAmount')
            ->andReturn(Money::zero('USD'))
            ->with($order->id)
            ->once();

        $dependencies->refundRepository
            ->shouldReceive('getRefundedParts')
            ->andReturn($refundedParts)
            ->with($order->id)
            ->once();

        $dependencies->refundRepository
            ->shouldReceive('getPartsToBeRefunded')
            ->andReturn(new Collection([$partToRefund]))
            ->with(array_keys($indexedPartToRefund))
            ->once();

        $dependencies->refundRepository
            ->shouldReceive('create')
            ->andReturn($expectedRefund)
            ->with([
                'order_id' => $order->id,
                'dealer_id' => $order->dealer_id,
                'amount' => $amountToRefund->getAmount(),
                'reason' => $reason,
                'parts' => collect($indexedPartToRefund)->values()->toArray()
            ])
            ->once();

        $dependencies->logger
            ->shouldReceive('info')
            ->once()
            ->with(
                sprintf('A refund process for {%d} order has begun', $order->id),
                ['id' => $order->id, 'amount' => $amountToRefund->getAmount(), 'parts' => $indexedPartToRefund, 'parts' => collect($indexedPartToRefund)->values()->toArray()]
            );

        $dependencies->paymentGatewayService
            ->shouldReceive('refund')
            ->andThrow($expectedException)
            ->with(
                $order->payment_intent,
                $amountToRefund,
                [['sku' => $partToRefund->sku, 'title' => $partToRefund->title, 'id' => $partToRefund->id, 'amount' => $amountToRefund->getAmount()->toFloat()]],
                $reason
            )
            ->once();

        $dependencies->logger
            ->shouldReceive('error')
            ->once();

        $dependencies->refundRepository
            ->shouldReceive('markAsFailed')
            ->with($expectedRefund, $expectedException->getMessage())
            ->once();

        $service = $this->getMockBuilder(PaymentService::class)
            ->setConstructorArgs($dependencies->getOrderedArguments())
            ->addMethods([]) // no mocked methods
            ->getMock();

        $this->expectException(MoneyMismatchException::class);
        $this->expectExceptionMessage($expectedException->getMessage());

        $service->refund($order->id, $amountToRefund, $indexedPartToRefund);
    }
}
