<?php

namespace App\Services\Ecommerce\CompletedOrder;

use App\Contracts\LoggerServiceInterface;
use App\Exceptions\Ecommerce\TextrailSyncException;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Models\Parts\Textrail\RefundedPart;
use App\Repositories\Ecommerce\CompletedOrderRepositoryInterface;
use App\Repositories\Ecommerce\RefundRepositoryInterface;
use App\Services\Ecommerce\DataProvider\Providers\TextrailWithCheckoutInterface;
use Brick\Money\Money;
use GuzzleHttp\Exception\ClientException;

class CompletedOrderService implements CompletedOrderServiceInterface
{
    /** @var CompletedOrderRepositoryInterface */
    private $completedOrderRepository;

    /** @var RefundRepositoryInterface */
    private $refundRepository;

    /** @var TextrailWithCheckoutInterface */
    private $textrailService;

    /** @var LoggerServiceInterface */
    private $logger;

    public function __construct(
        CompletedOrderRepositoryInterface $completedOrderRepository,
        RefundRepositoryInterface         $refundRepository,
        TextrailWithCheckoutInterface     $textrailService,
        LoggerServiceInterface            $logger
    )
    {
        $this->completedOrderRepository = $completedOrderRepository;
        $this->refundRepository = $refundRepository;
        $this->textrailService = $textrailService;
        $this->logger = $logger;
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

    /**
     * @param int $orderId TC ecommerce order id
     * @return int the TexTrail order id
     * @throws \App\Exceptions\Ecommerce\TextrailSyncException when some thing goes wrong on Magento side
     */
    public function syncSingleOrderOnTextrail(int $orderId): int
    {
        $this->logger->info(sprintf('Starting order Magento syncer for: %d', $orderId));

        /** @var CompletedOrder $order */
        $order = $this->completedOrderRepository->get(['id' => $orderId]);

        if ($order->ecommerce_order_id) {
            throw new TextrailSyncException(sprintf('The order %d has already been synced to TexTrail', $orderId));
        }

        try {
            $this->completedOrderRepository->beginTransaction();

            // just in case we need to covert a customer cart into an order, we should use another method like createOrderFromCart
            //$method = $order->ecommerce_customer_id ? 'createOrderFromCart' : 'createOrderFromGuestCart';

            $poNumber = $this->completedOrderRepository->generateNextPoNumber($order->dealer_id);

            $texTrailOrderId = $this->textrailService->createOrderFromGuestCart($order->ecommerce_cart_id, $poNumber);

            $this->completedOrderRepository->update(['id' => $orderId, 'ecommerce_order_id' => $texTrailOrderId, 'po_number' => $poNumber]);

            $this->completedOrderRepository->commitTransaction();

            $this->logger->info(
                sprintf('Magento order was successfully create for: %d', $orderId),
                ['ecommerce_order_id' => $texTrailOrderId]
            );

            return $texTrailOrderId;
        } catch (ClientException | \Exception $exception) {
            $message = $exception instanceof ClientException && $exception->getResponse() ?
                json_decode($exception->getResponse()->getBody()->getContents(), true) :
                $exception->getMessage();

            $this->completedOrderRepository->rollbackTransaction();

            $this->logger->critical($exception->getMessage());

            $this->completedOrderRepository->logError(
                $order->id,
                $message,
                CompletedOrder::ERROR_STAGE_TEXTRAIL_REMOTE_SYNC
            );

            throw new TextrailSyncException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
