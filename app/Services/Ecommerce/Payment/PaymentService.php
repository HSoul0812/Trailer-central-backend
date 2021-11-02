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
        RefundRepositoryInterface         $refundRepository,
        CompletedOrderRepositoryInterface $orderRepository,
        CompletedOrderServiceInterface    $orderService,
        PaymentGatewayServiceInterface    $paymentGatewayService,
        LoggerServiceInterface            $logger
    )
    {
        $this->refundRepository = $refundRepository;
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
        $this->paymentGatewayService = $paymentGatewayService;
        $this->logger = $logger;
    }

    /**
     * @param int $id
     * @param Money $amount
     * @param array{id: int, amount: float} $parts parts to be refunded indexed by id
     * @param string|null $reason
     * @return Refund
     *
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws RefundAmountException when the order is not refundable due it is unpaid
     * @throws RefundAmountException when the order is not refundable due it is refunded
     * @throws RefundAmountException when the amount is greater than its balance
     * @throws RefundAmountException when the order cannot be refunded due some provided part amount is greater than its remaining balance
     * @throws RefundAmountException when the order cannot be refunded due some provided part amount is greater than the paid amount for that part
     * @throws RefundAmountException when the order cannot be refunded due some provided part is not a placed part
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
        $refund = $this->createRefund($order, $amount, collect($parts)->values()->toArray(), $reason);

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
     * @param CompletedOrder $order
     * @param Money $amount
     * @param array{id: int, amount: float} $parts parts to be refunded indexed by id
     *
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws RefundAmountException when the order is not refundable due it is unpaid
     * @throws RefundAmountException when the order is not refundable due it is refunded
     * @throws RefundAmountException when the amount is greater than its balance
     * @throws RefundAmountException when the order cannot be refunded due some provided part amount is greater than its remaining balance
     * @throws RefundAmountException when the order cannot be refunded due some provided part amount is greater than the paid amount for that part
     * @throws RefundAmountException when the order cannot be refunded due some provided part is not a placed part
     * @throws RefundException when a provided part was not a placed part
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

        $indexedOrderParts = collect($order->parts)->keyBy('id')->toArray();

        if (empty($indexedOrderParts) && !empty($parts)) {
            throw new RefundException(
                sprintf(
                    '%d order cannot be refunded due it has not a related parts matching with the request',
                    $order->id
                )
            );
        }

        $refundedParts = $this->refundRepository->getRefundedParts($order->id)->keyBy('id')->toArray();
        $partsTotal = Money::zero('USD');

        foreach ($parts as $part) {
            ['id' => $partId, 'amount' => $partAmount] = $part;

            $partsTotal = $partsTotal->plus(Money::of($partAmount, 'USD', null, RoundingMode::UP));

            if (array_key_exists($partId, $indexedOrderParts)) {
                // a part item is the price of the part multiplied by the quantity
                $partSubTotal = Money::of(
                    $indexedOrderParts[$partId]['qty'] * $indexedOrderParts[$partId]['price'],
                    'USD',
                    null,
                    RoundingMode::UP
                );

                // check if the refunded amount will be greater than the total paid for the part item
                if (isset($refundedParts[$partId])
                    && Money::of($refundedParts[$partId]->amount + $partAmount, 'USD', null, RoundingMode::UP)->isGreaterThan($partSubTotal)
                ) {
                    throw new RefundAmountException(
                        sprintf(
                            '%d order cannot be refunded due the provided amount %f for the part %d is greater than its balance',
                            $order->id,
                            $partAmount,
                            $partId
                        )
                    );
                } elseif (!isset($refundedParts[$partId]) &&
                    Money::of($partAmount, 'USD', null, RoundingMode::UP)->isGreaterThan($partSubTotal)) {
                    throw new RefundAmountException(
                        sprintf(
                            '%d order cannot be refunded due the provided amount %.2f for the part %d is greater than the paid amount for the part',
                            $order->id,
                            $partAmount,
                            $partId
                        )
                    );
                }
            } else {
                throw new RefundAmountException(
                    sprintf(
                        '%d order cannot be refunded due the provided part %d is not a placed part',
                        $order->id,
                        $partId
                    )
                );
            }
        }

        if ($partsTotal->isGreaterThan($amount)) {
            throw new RefundAmountException(
                sprintf(
                    '%d order cannot be refunded due the provided amount %.2f is less than the total amount of the parts',
                    $order->id,
                    $amount->getAmount()->toFloat()
                )
            );
        }
    }

    /**
     * @param CompletedOrder $order
     * @param Money $amount
     * @param array<int> $parts part's ids to be refunded
     * @param null|string $reason
     *
     * @return Refund
     * @throws \Exception when some error has occurred on DB saving time
     */
    private function createRefund(
        CompletedOrder $order,
        Money          $amount,
        array          $parts,
        ?string        $reason = null
    ): Refund
    {
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
     * @param Refund $refund
     * @param StripeRefundResult $gatewayRefundResponse
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
     * @param array{id: int, amount: float} $parts parts to be refunded indexed by id
     * @return array{sku:string, title:string, id:int, amount: float}
     */
    private function encodePartsToBeRefunded(array $parts): array
    {
        return $this->refundRepository
            ->getPartsToBeRefunded(array_keys($parts))
            ->map(static function (Part $part) use ($parts): array {
                return ['sku' => $part->sku, 'title' => $part->title, 'id' => $part->id, 'amount' => $parts[$part->id]['amount']];
            })->toArray();
    }
}
