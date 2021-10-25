<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\Payment;

use App\Contracts\LoggerServiceInterface;
use App\Exceptions\Ecommerce\AfterRemoteRefundException;
use App\Exceptions\Ecommerce\RefundAmountException;
use App\Exceptions\Ecommerce\RefundException;
use App\Exceptions\Ecommerce\RefundPaymentGatewayException;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Models\Ecommerce\Refund;
use App\Models\Parts\Textrail\Part;
use App\Repositories\Ecommerce\CompletedOrderRepositoryInterface;
use App\Repositories\Ecommerce\RefundRepositoryInterface;
use App\Services\Ecommerce\CompletedOrder\CompletedOrderServiceInterface;
use App\Services\Ecommerce\Payment\Gateways\PaymentGatewayServiceInterface;
use App\Services\Ecommerce\Payment\Gateways\Stripe\StripeRefundResult;
use Brick\Math\RoundingMode;
use Brick\Money\Money;

class PaymentService implements PaymentServiceInterface
{
    /** @var RefundRepositoryInterface */
    private $refundRepository;

    /** @var CompletedOrderRepositoryInterface */
    private $orderRepository;

    /** @var CompletedOrderServiceInterface */
    private $orderService;

    /** @var PaymentGatewayServiceInterface */
    private $paymentGatewayService;

    /** @var LoggerServiceInterface */
    private $logger;

    public function __construct(
        RefundRepositoryInterface $refundRepository,
        CompletedOrderRepositoryInterface $orderRepository,
        CompletedOrderServiceInterface $orderService,
        PaymentGatewayServiceInterface $paymentGatewayService,
        LoggerServiceInterface $logger
    ) {
        $this->refundRepository = $refundRepository;
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
        $this->paymentGatewayService = $paymentGatewayService;
        $this->logger = $logger;
    }

    /**
     * @param  int  $id
     * @param  Money  $amount
     * @param  array<int>  $parts  part's ids to be refunded
     * @param  string|null  $reason
     * @return Refund
     *
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws RefundAmountException when the order is not refundable due it is unpaid
     * @throws RefundAmountException when the order is not refundable due it is refunded
     * @throws RefundAmountException when the amount is greater than its balance
     * @throws RefundException when a provided part was not a placed part
     * @throws RefundException when a provided part was already refunded
     * @throws RefundException when the order has not a related parts matching with the request
     * @throws RefundException when the order it has not a payment unique id
     * @throws \Exception when some error has occurred on DB saving time
     * @throws RefundException when there was some error on payment gateway local process
     * @throws RefundPaymentGatewayException when there was some error on payment gateway remote process
     * @throws AfterRemoteRefundException when there was some error after the refund was successfully done on payment gateway side
     */
    public function refund(int $id, Money $amount, array $parts, ?string $reason = null): Refund
    {
        $order = $this->orderRepository->get(['id' => $id]);

        $this->ensureOrderCanBeRefunded($order, $amount, $parts);

        $encodedParts = $this->encodePartsToBeRefunded($parts);

        // Some issuable context information
        $logContext = ['id' => $id, 'amount' => $amount->getAmount(), 'parts' => $encodedParts];

        // This logic must not be a transaction to be able recuperating from an error after the refund has been
        // successfully created in the payment gateway side
        $refund = $this->createRefund($order, $amount, $parts, $reason);

        try {
            $gatewayRefundResponse = $this->paymentGatewayService->refund(
                $order->payment_intent,
                $amount,
                $encodedParts,
                $reason
            );

            $this->tryToFinishRefund($refund, $gatewayRefundResponse);

            return $refund;
        } catch (RefundPaymentGatewayException $exception) {
            $errorMessage = sprintf(
                'The refund {%d} for {%d} order had a remote process error: %s',
                $refund->id,
                $id,
                $exception->getMessage()
            );

            $this->logger->error($errorMessage, $logContext);

            $this->refundRepository->markAsFailed($refund, $exception->getMessage());

            throw $exception;
        } catch (AfterRemoteRefundException $exception) {
            $this->logger->critical(
                $exception->getMessage(),
                array_merge($exception->getContext(), ['response' => $exception->getResult()->asArray()])
            );

            // This is a naive approach given that if there was a failure when it tried to finish the refund (post-gateway),
            // then, it probably will fail again here, but at least the critical log was recorded, and the subsequent
            // refund attempt will be prevented
            $this->refundRepository->markAsRecoverableFailure($refund, $exception->getResult());

            throw $exception;
        } catch (\Exception  $exception) {
            $errorMessage = sprintf(
                'The refund {%d} for {%d} order had a local error: %s',
                $refund->id,
                $id,
                $exception->getMessage()
            );

            $this->logger->error($errorMessage, $logContext);

            $this->refundRepository->markAsFailed($refund, $exception->getMessage());

            throw $exception;
        }
    }

    /**
     * @param  CompletedOrder  $order
     * @param  Money  $amount
     * @param  array<int>  $parts  part's ids to be refunded
     *
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws RefundAmountException when the order is not refundable due it is unpaid
     * @throws RefundAmountException when the order is not refundable due it is refunded
     * @throws RefundAmountException when the amount is greater than its balance
     * @throws RefundException when a provided part was not a placed part
     * @throws RefundException when a provided part was already refunded
     * @throws RefundException when the order has not a related parts matching with the request
     * @throws RefundException when the order it has not a payment unique id
     */
    private function ensureOrderCanBeRefunded(CompletedOrder $order, Money $amount, array $parts): void
    {
        if ($order->isUnpaid()) {
            throw new RefundAmountException(sprintf('%d order is not refundable due it is unpaid', $order->id));
        }

        if (!$order->isRefundable()) {
            throw new RefundAmountException(sprintf('%d order is not refundable due it is refunded', $order->id));
        }

        if (empty($order->payment_intent)) {
            throw new RefundException(
                sprintf('%d order is not refundable due it has not a payment unique id', $order->id)
            );
        }

        $refundedAmount = $this->refundRepository->getRefundedAmount($order->id);

        $orderTotalAmount = Money::of(
            $order->total_amount,
            'USD',
            null,
            RoundingMode::DOWN
        );

        if ($orderTotalAmount
            ->minus($refundedAmount)
            ->minus($amount)
            ->isLessThan(0)) {
            throw new RefundAmountException(
                sprintf('%d order is not refundable due the amount is greater than its balance', $order->id)
            );
        }

        $orderParts = collect($order->parts)->map(static function (array $part): int {
            return $part['id'];
        })->toArray();

        if (empty($orderParts) && !empty($parts)) {
            throw new RefundException(
                sprintf(
                    '%d order cannot be refunded due it has not a related parts matching with the request',
                    $order->id
                )
            );
        }

        collect($parts)->each(static function (int $partId) use ($orderParts, $order) {
            if (!empty($orderParts) && !in_array($partId, $orderParts)) {
                throw new RefundException(
                    sprintf(
                        '%d order cannot be refunded due the provided part %d is not a placed part',
                        $order->id,
                        $partId
                    )
                );
            }
        });

        $refundedParts = $this->refundRepository->getRefundedParts($order->id)->map(static function (Part $part): int {
            return $part->id;
        })->toArray();

        if (!empty(array_intersect($refundedParts, $parts))) {
            throw new RefundException(
                sprintf('%d order cannot be refunded due some provided part was already refunded', $order->id)
            );
        }
    }

    /**
     * @param  CompletedOrder  $order
     * @param  Money  $amount
     * @param  array<int>  $parts  part's ids to be refunded
     * @param  null|string  $reason
     *
     * @return Refund
     * @throws \Exception when some error has occurred on DB saving time
     */
    private function createRefund(
        CompletedOrder $order,
        Money $amount,
        array $parts,
        ?string $reason = null
    ): Refund {
        $refund = $this->refundRepository->create([
            'order_id' => $order->id,
            'amount' => $amount->getAmount(),
            'reason' => $reason,
            'parts' => $parts
        ]);

        $this->logger->info(
            sprintf('A refund process for {%d} order has begun', $order->id),
            ['id' => $order->id, 'amount' => $amount->getAmount(), 'parts' => $parts]
        );

        return $refund;
    }

    /**
     * @param  Refund  $refund
     * @param  StripeRefundResult  $gatewayRefundResponse
     *
     * @throws AfterRemoteRefundException when there was some error after the refund was successfully done on payment gateway side
     */
    private function tryToFinishRefund(Refund $refund, StripeRefundResult $gatewayRefundResponse): void
    {
        $logContext = [
            'id' => $refund->order_id,
            'refund_id' => $refund->id,
            'amount' => $refund->amount,
            'parts' => $refund->parts
        ];

        try {
            $this->orderRepository->beginTransaction();

            $this->refundRepository->markAsFinished($refund, $gatewayRefundResponse);
            $this->orderService->updateRefundSummary($refund->order_id);

            $this->logger->info(
                sprintf('The refund {%d} for {%d} order was successfully done', $refund->id, $refund->order_id),
                $logContext
            );

            $this->orderRepository->commitTransaction();
        } catch (\Exception $exception) {
            $this->orderRepository->rollbackTransaction();

            $exception = new AfterRemoteRefundException(sprintf(
                'The refund {%d} for {%d} order had a critical error after its remote process: %s',
                $refund->id,
                $refund->order_id,
                $exception->getMessage()
            ));

            // just in case any error has occurred after a successful gateway refund, we need to store that
            // response payload somewhere for monitoring purpose, or any refund issue
            // @todo: many payment gateways doesn't allow cancelling refunds, so this should be a charge to rollback the refund

            throw $exception->withContext($logContext)->withResult($gatewayRefundResponse);
        }
    }

    /**
     * @param  array<int>  $parts  part's ids to be refunded
     * @return array{sku:string, title:string, id:int}
     */
    private function encodePartsToBeRefunded(array $parts): array
    {
        return $this->refundRepository
            ->getPartsToBeRefunded($parts)
            ->map(static function (Part $part): array {
                return ['sku' => $part->sku, 'title' => $part->title, 'id' => $part->id];
            })->toArray();
    }
}
