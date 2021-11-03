<?php

namespace App\Services\Ecommerce\CompletedOrder;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Models\Parts\Textrail\RefundedPart;
use App\Repositories\Ecommerce\CompletedOrderRepositoryInterface;
use App\Repositories\Ecommerce\RefundRepositoryInterface;
use App\Repositories\Parts\PartRepositoryInterface;
use Brick\Money\Money;

class CompletedOrderService implements CompletedOrderServiceInterface
{

    /** @var CompletedOrderRepositoryInterface */
    private $completedOrderRepository;

    /** @var PartRepositoryInterface */
    private $textRailPartRepository;

    /** @var RefundRepositoryInterface */
    private $refundRepository;

    public function __construct(
        CompletedOrderRepositoryInterface $completedOrderRepository,
        PartRepositoryInterface $textRailPartRepository,
        RefundRepositoryInterface $refundRepository
    ) {
        $this->completedOrderRepository = $completedOrderRepository;
        $this->textRailPartRepository = $textRailPartRepository;
        $this->refundRepository = $refundRepository;
    }

    public function create(array $params): CompletedOrder
    {
        return $this->completedOrderRepository->create($params);
    }

    /**
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    public function updateRefundSummary(int $orderId): bool
    {
        /** @var CompletedOrder $order */
        $order = $this->completedOrderRepository->get(['id' => $orderId]);

        $refundedAmount = $this->refundRepository->getRefundedAmount($orderId);
        $orderTotalAmount = Money::of($order->total_amount, 'USD');

        $refund_status = null;

        if ($refundedAmount->isEqualTo($orderTotalAmount)) {
            $refund_status = CompletedOrder::REFUND_STATUS_REFUNDED;
        }

        if ($refundedAmount->isLessThan($orderTotalAmount) && $refundedAmount->isGreaterThan(Money::zero('USD'))) {
            $refund_status = CompletedOrder::REFUND_STATUS_PARTIAL_REFUNDED;
        }

        $refundedParts = $this->refundRepository->getRefundedParts($orderId)->map(static function (RefundedPart $part): array {
            return $part->asArray();
        })->toArray();

        $refundedAmount = $this->refundRepository->getRefundedAmount($orderId);

        return $refund_status && $this->completedOrderRepository->update([
                    'id' => $orderId,
                    'refund_status' => $refund_status,
                    'refunded_parts' => $refundedParts,
                    'refunded_amount' => $refundedAmount->getAmount()
                ]
            );
    }
}
