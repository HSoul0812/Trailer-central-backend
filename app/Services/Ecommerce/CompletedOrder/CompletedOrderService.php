<?php

namespace App\Services\Ecommerce\CompletedOrder;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Models\Parts\Textrail\RefundedPart;
use App\Repositories\Ecommerce\CompletedOrderRepositoryInterface;
use App\Repositories\Ecommerce\RefundRepositoryInterface;
use Brick\Money\Money;

class CompletedOrderService implements CompletedOrderServiceInterface
{
    /** @var CompletedOrderRepositoryInterface */
    private $completedOrderRepository;

    /** @var RefundRepositoryInterface */
    private $refundRepository;

    public function __construct(
        CompletedOrderRepositoryInterface $completedOrderRepository,
        RefundRepositoryInterface         $refundRepository
    )
    {
        $this->completedOrderRepository = $completedOrderRepository;
        $this->refundRepository = $refundRepository;
    }

    public function create(array $params): CompletedOrder
    {
        return $this->completedOrderRepository->create($params);
    }

    public function updateRefundSummary(int $orderId): bool
    {
        /** @var CompletedOrder $order */
        $order = $this->completedOrderRepository->get(['id' => $orderId]);

        $orderRefundSummary = $this->refundRepository->getOrderRefundSummary($orderId);

        $refundedAmount = $orderRefundSummary['parts_amount']->plus($orderRefundSummary['adjustment_amount'])
            ->plus($orderRefundSummary['handling_amount'])
            ->plus($orderRefundSummary['shipping_amount'])
            ->plus($orderRefundSummary['tax_amount']);

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

        return $refund_status && $this->completedOrderRepository->update([
                    'id' => $orderId,
                    'refund_status' => $refund_status,
                    'refunded_parts' => $refundedParts,
                    'parts_refunded_amount' => $orderRefundSummary['parts_amount']->getAmount(),
                    'adjustment_refunded_amount' => $orderRefundSummary['adjustment_amount']->getAmount(),
                    'handling_refunded_amount' => $orderRefundSummary['handling_amount']->getAmount(),
                    'shipping_refunded_amount' => $orderRefundSummary['shipping_amount']->getAmount(),
                    'tax_refunded_amount' => $orderRefundSummary['tax_amount']->getAmount(),
                    'total_refunded_amount' => $refundedAmount->getAmount()
                ]
            );
    }
}
