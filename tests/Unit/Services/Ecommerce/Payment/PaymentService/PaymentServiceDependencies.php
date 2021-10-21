<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Ecommerce\Payment\PaymentService;

use App\Contracts\LoggerServiceInterface;
use App\Repositories\Ecommerce\CompletedOrderRepositoryInterface;
use App\Repositories\Ecommerce\RefundRepositoryInterface;
use App\Services\Ecommerce\CompletedOrder\CompletedOrderServiceInterface;
use App\Services\Ecommerce\Payment\Gateways\PaymentGatewayServiceInterface;
use Mockery;

class PaymentServiceDependencies
{
    /** @var RefundRepositoryInterface|Mockery\LegacyMockInterface|Mockery\MockInterface */
    public $refundRepository;

    /** @var CompletedOrderRepositoryInterface|Mockery\LegacyMockInterface|Mockery\MockInterface */
    public $orderRepository;

    /** @var CompletedOrderServiceInterface|Mockery\LegacyMockInterface|Mockery\MockInterface */
    public $orderService;

    /** @var PaymentGatewayServiceInterface|Mockery\LegacyMockInterface|Mockery\MockInterface */
    public $paymentGatewayService;

    /** @var LoggerServiceInterface|Mockery\LegacyMockInterface|Mockery\MockInterface */
    public $logger;

    public function __construct()
    {
        $this->refundRepository = Mockery::mock(RefundRepositoryInterface::class);
        $this->orderRepository = Mockery::mock(CompletedOrderRepositoryInterface::class);
        $this->orderService = Mockery::mock(CompletedOrderServiceInterface::class);
        $this->paymentGatewayService = Mockery::mock(PaymentGatewayServiceInterface::class);
        $this->logger = Mockery::mock(LoggerServiceInterface::class);
    }

    public function getOrderedArguments(): array
    {
        return [
            $this->refundRepository,
            $this->orderRepository,
            $this->orderService,
            $this->paymentGatewayService,
            $this->logger
        ];
    }
}
