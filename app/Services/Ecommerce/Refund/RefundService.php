<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\Refund;

use App\Contracts\LoggerServiceInterface;
use App\Exceptions\Ecommerce\RefundFailureException;
use App\Exceptions\Ecommerce\RefundAmountException;
use App\Exceptions\Ecommerce\RefundException;
use App\Exceptions\Ecommerce\RefundHttpClientException;
use App\Exceptions\Ecommerce\RefundPaymentGatewayException;
use App\Jobs\Ecommerce\ProcessRefundOnPaymentGatewayJob;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Psr\Http\Message\ResponseInterface;
use App\Models\Ecommerce\Refund;
use App\Repositories\Ecommerce\CompletedOrderRepositoryInterface;
use App\Repositories\Ecommerce\RefundRepositoryInterface;
use App\Services\Ecommerce\CompletedOrder\CompletedOrderServiceInterface;
use App\Services\Ecommerce\DataProvider\Providers\TextrailRefundsInterface;
use App\Services\Ecommerce\Payment\Gateways\PaymentGatewayServiceInterface;
use App\Services\Ecommerce\Payment\Gateways\PaymentGatewayRefundResultInterface;
use Brick\Money\Money;

class RefundService implements RefundServiceInterface
{
    use DispatchesJobs;

    /** @var RefundRepositoryInterface */
    private $refundRepository;

    /** @var CompletedOrderRepositoryInterface */
    private $orderRepository;

    /** @var CompletedOrderServiceInterface */
    private $orderService;

    /** @var TextrailRefundsInterface */
    private $textrailService;

    /** @var PaymentGatewayServiceInterface */
    private $paymentGatewayService;

    /** @var LoggerServiceInterface */
    private $logger;

    public function __construct(
        RefundRepositoryInterface         $refundRepository,
        CompletedOrderRepositoryInterface $orderRepository,
        CompletedOrderServiceInterface    $orderService,
        TextrailRefundsInterface          $textrailService,
        PaymentGatewayServiceInterface    $paymentGatewayService,
        LoggerServiceInterface            $logger
    )
    {
        $this->refundRepository = $refundRepository;
        $this->orderRepository = $orderRepository;
        $this->textrailService = $textrailService;
        $this->paymentGatewayService = $paymentGatewayService;
        $this->orderService = $orderService;
        $this->logger = $logger;
    }

    /**
     * It will create a full/partial refund in our database, then it will send a refund/memo request to TexTrail,
     * but the refund process on the payment gateway will be remaining as pending until TextTrail send us a command to proceed.
     *
     * @param RefundBag $refundBag
     * @return Refund
     *
     * @throws RefundException when the order is not refundable due it is unpaid
     * @throws RefundException when the order is not refundable due it is refunded
     * @throws RefundException when the order is not refundable due it is canceled
     * @throws RefundException when the order has not a payment unique id
     * @throws RefundException when the order has not a related parts matching with the request
     * @throws RefundAmountException when the refund total amount is greater than the order remaining total balance
     * @throws RefundAmountException when the refund parts amount is greater than the order remaining parts balance
     * @throws RefundAmountException when the refund handling amount is greater than the order remaining handling balance
     * @throws RefundAmountException when the refund shipping amount is greater than the order remaining shipping balance
     * @throws RefundAmountException when the refund tax amount is greater than the order remaining tax balance
     * @throws RefundAmountException when some provided part qty is greater than the remaining qty
     * @throws RefundAmountException when some provided part qty is greater than the purchase qty
     * @throws RefundException when a provided part was not a placed part in the order
     * @throws \Exception when some error has occurred on DB saving time
     * @throws \Exception when some error has occurred trying to send the refund request to TexTrail
     * @throws RefundException when there was some error on payment TexTrail local process
     * @throws RefundHttpClientException when there was some error on Textrail remote process
     * @throws RefundFailureException when there was some error after the refund was successfully received by Textrail
     */
    public function issue(RefundBag $refundBag): Refund
    {
        $refundBag->validate();

        // Some issuable context information
        $logContext = $refundBag->asArray();

        // This logic must not be a transaction to be able recuperating from an error after the successfully
        // received Textrail refund request
        $refund = $this->createRefund($refundBag);

        /** @var int $textrailRma */

        try {
            $textrailRma = $this->textrailService->requestReturn($refundBag);

            $this->updateOrderRefundSummary($refund, $textrailRma);

            $this->refundRepository->updateRma($refund, $textrailRma);

            return $refund;
        } catch (ClientException $clientException) {
            /** @var ResponseInterface $response */
            $response = $clientException->getResponse();

            $exception = RefundHttpClientException::factory($clientException->getMessage());

            $message = $exception->getMessage();

            if ($response) {
                $message = $response->getBody()->getContents(); // to prevent any message truncation

                $logContext['response'] = ['body' => $message, 'status' => $response->getStatusCode()];

                $exception = RefundHttpClientException::factory(
                    $message,
                    $response->getStatusCode(),
                    $message,
                    null,
                    $response->getHeaders()
                );
            }

            $this->logger->error($message, $logContext);

            $this->refundRepository->markAsFailed($refund, $message, Refund::ERROR_STAGE_TEXTRAIL_ISSUE_REMOTE);

            throw $exception;
        } catch (RefundFailureException $exception) {
            $this->logger->critical(
                $exception->getMessage(),
                array_merge($logContext, ['textrail_rma' => $textrailRma])
            );

            // This is a naive approach given that if there was a failure when it tried to finish the refund (post-textrail)
            // then, it probably will fail again here, but at least the critical log was recorded, and the subsequent
            // refund attempt will be prevented
            $this->refundRepository->markAsRecoverableFailure(
                $refund,
                ['textrail_rma' => $textrailRma],
                $exception->getMessage(),
                Refund::RECOVERABLE_STAGE_TEXTRAIL_ISSUE
            );

            throw $exception;
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), $logContext);

            $this->refundRepository->markAsFailed(
                $refund,
                $exception->getMessage(),
                Refund::ERROR_STAGE_TEXTRAIL_ISSUE_LOCAL
            );

            throw $exception;
        }
    }

    /**
     * It will create a full refund in the database, then it should enqueue a refund process on the payment gateway
     * when it reaches the `return_receive` status.
     *
     * @param RefundBag $refundBag
     * @return Refund
     *
     * @throws RefundException when the order is not refundable due it is unpaid
     * @throws RefundException when the order is not refundable due it is refunded
     * @throws RefundException when the order is not refundable due it is canceled
     * @throws RefundException when the order has not a payment unique id
     * @throws RefundException when the order has not a related parts matching with the request
     * @throws RefundAmountException when the refund total amount is greater than the order remaining total balance
     * @throws RefundAmountException when the refund parts amount is greater than the order remaining parts balance
     * @throws RefundAmountException when the refund handling amount is greater than the order remaining handling balance
     * @throws RefundAmountException when the refund shipping amount is greater than the order remaining shipping balance
     * @throws RefundAmountException when the refund tax amount is greater than the order remaining tax balance
     * @throws RefundAmountException when some provided part qty is greater than the remaining qty
     * @throws RefundAmountException when some provided part qty is greater than the purchase qty
     * @throws RefundException when a provided part was not a placed part in the order
     * @throws \Exception when some unknown exception has occurred
     * @throws RefundFailureException when there was some error after the refund was successfully received by Textrail
     */
    public function cancelOrder(RefundBag $refundBag): Refund
    {
        $refundBag->validate();

        // Some issuable context information
        $logContext = $refundBag->asArray() + ['textrail_order_id' => $refundBag->order->ecommerce_order_id];

        // This logic must not be a transaction to be able recuperating from an error after it has been successfully
        // received from Textrail
        $refund = $this->createRefund($refundBag);

        try {
            $this->orderRepository->update(['id' => $refundBag->order->id, 'ecommerce_order_status' => CompletedOrder::ECOMMERCE_STATUS_CANCELED]);

            $this->updateOrderRefundSummary($refund);

            $this->dispatchPaymentGatewayRefundJob($refund);

            return $refund;
        } catch (\Exception $exception) {
            $this->logger->critical(
                $exception->getMessage(),
                $logContext
            );

            // This is a naive approach given that if there was a failure when it tried to finish the refund (post-textrail),
            // then, it probably will fail again here, but at least the critical log was recorded, and the subsequent
            // refund attempt will be prevented
            $this->refundRepository->markAsRecoverableFailure(
                $refund,
                ['textrail_order_id' => $refundBag->order->ecommerce_order_id],
                $exception->getMessage(),
                Refund::RECOVERABLE_STAGE_TEXTRAIL_ISSUE
            );

            throw $exception;
        }
    }

    /**
     * @param Refund $refund
     * @param string $status
     * @param array<array{sku: string, qty: int}> $parts array of parts indexed by part sku
     * @return bool
     * @throws RefundFailureException when it was not possible to mark it as rejected
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    public function updateStatus(Refund $refund, string $status, array $parts): bool
    {
        if (!in_array($status, [Refund::STATUS_REJECTED, Refund::STATUS_AUTHORIZED, Refund::STATUS_RETURN_RECEIVED])) {
            throw new \InvalidArgumentException(sprintf("The refund status '%s' is not a valid status", $status));
        }

        if (!$refund->canMoveStatusTo($status)) {
            throw new RefundException(
                sprintf("Refund status cannot move from '%s' to '%s'", $refund->status, $status));
        }

        if ($status === Refund::STATUS_REJECTED) {
            return $this->markAsRejected($refund, $refund->parts);
        }

        $recalculatedParts = $this->reCalculateParts(collect($refund->parts)->keyBy('sku')->toArray(), $parts);
        $refund->parts_amount = $recalculatedParts['partsAmount']->getAmount()->toFloat(); // reset the parts amount

        if ($status === Refund::STATUS_AUTHORIZED) {
            return $this->markAsAuthorized($refund, $recalculatedParts['originalParts'], $recalculatedParts['updatedParts']);
        }

        return $this->markAsReturnReceived($refund, $recalculatedParts['updatedParts']);
    }

    /**
     * It will call the refund process on the payment gateway.
     *
     * @param int $refundId
     * @throws RefundPaymentGatewayException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function refund(int $refundId): void
    {
        $refund = $this->refundRepository->get($refundId);

        if ($refund) {
            // Some issuable context information
            $logContext = collect($refund->toArray())
                ->except(['created_at', 'updated_at', 'failed_at', 'metadata'])
                ->toArray();

            /** @var PaymentGatewayRefundResultInterface $gatewayRefundResponse */

            try {
                $gatewayRefundResponse = $this->paymentGatewayService->refund(
                    $refund->order->payment_intent,
                    Money::of($refund->total_amount, 'USD'),
                    $refund->parts,
                    $refund->reason
                );

                $this->refundRepository->markAsCompleted($refund, $gatewayRefundResponse);

                return;
            } catch (RefundPaymentGatewayException $exception) {
                $errorMessage = sprintf(
                    'The refund {%d} for {%d} order had a remote process error: %s',
                    $refund->id,
                    $refund->order_id,
                    $exception->getMessage()
                );

                $this->logger->error($errorMessage, $logContext);

                $this->refundRepository->markAsFailed(
                    $refund, $exception->getMessage(),
                    Refund::ERROR_STAGE_PAYMENT_GATEWAY_REFUND_LOCAL
                );

                throw $exception;
            } catch (\Exception  $exception) {
                $this->logger->critical(
                    $exception->getMessage(),
                    array_merge($logContext, ['response' => $gatewayRefundResponse->asArray()])
                );

                // This is a naive approach given that if there was a failure when it tried to finish the refund (post-gateway),
                // then, it probably will fail again here, but at least the critical log was recorded, and the subsequent
                // refund attempt will be prevented
                $this->refundRepository->markAsRecoverableFailure(
                    $refund,
                    $gatewayRefundResponse->asArray(),
                    $exception->getMessage(),
                    Refund::RECOVERABLE_STAGE_PAYMENT_GATEWAY_REFUND
                );

                throw new RefundPaymentGatewayException($exception->getMessage(), $exception->getCode(), $exception);
            }
        }

        throw new ModelNotFoundException(sprintf('No query results for model [%s] %s', Refund::class, $refundId));
    }

    /**
     * @param Refund $refund
     * @param int|null $textrailRma
     *
     * @throws RefundFailureException
     */
    private function updateOrderRefundSummary(Refund $refund, ?int $textrailRma = null): void
    {
        // Some issuable context information
        $logContext = collect($refund->toArray())
            ->except(['created_at', 'updated_at', 'failed_at', 'metadata'])
            ->toArray();

        try {
            $this->orderRepository->beginTransaction();

            $this->orderService->updateRefundSummary($refund->order_id);

            $this->logger->info(
                sprintf('The refund {%d} for {%d} order was successfully send to TexTrail', $refund->id, $refund->order_id),
                $logContext
            );

            $this->orderRepository->commitTransaction();
        } catch (\Exception $exception) {
            $this->orderRepository->rollbackTransaction();

            $exception = new RefundFailureException(sprintf(
                'The refund {%d} for {%d} order had a critical error after the TexTrail remote process: %s',
                $refund->id,
                $refund->order_id,
                $exception->getMessage()
            ));

            // just in case any error has occurred after a successful gateway refund, we need to store that
            // response payload somewhere for monitoring purpose, or any refund issue
            // @todo: many payment gateways doesn't allow cancelling refunds, so this should be a charge to rollback the refund

            throw $exception->withContext($logContext)->withTextrailRma($textrailRma);
        }
    }

    /**
     * @param RefundBag $refundBag
     *
     * @return Refund
     * @throws \Exception when some error has occurred on DB saving time
     */
    private function createRefund(RefundBag $refundBag): Refund
    {
        $refund = $this->refundRepository->create(['dealer_id' => $refundBag->order->dealer_id] + $refundBag->asArray());

        $this->logger->info(
            sprintf('A refund process for {%d} order has begun', $refundBag->order->id),
            $refundBag->asArray()
        );

        return $refund;
    }

    /**
     * @param Refund $refund
     */
    private function dispatchPaymentGatewayRefundJob(Refund $refund): void
    {
        $this->refundRepository->markAsProcessing($refund);

        $job = new ProcessRefundOnPaymentGatewayJob($refund->id);
        $this->dispatch($job->onQueue(config('ecommerce.textrail.queue')));
    }

    /**
     * When a return has been rejected, it should restore refunded order amounts and its refunded parts
     *
     * @param Refund $refund
     * @param array<array{sku: string, qty: int}> $parts array of parts indexed by part sku
     * @return bool
     * @throws RefundFailureException when it was not possible to mark it as rejected
     */
    private function markAsRejected(Refund $refund, array $parts): bool
    {
        $logContext = ['id' => $refund->id, 'rma' => $refund->textrail_rma];

        try {
            $this->orderRepository->beginTransaction();

            $this->refundRepository->markAsRejected($refund, $refund->parts);

            $this->orderService->updateRefundSummary($refund->order_id);

            $this->logger->info(
                sprintf(
                    'The refund {%d} for {%d} order was successfully marked as rejected',
                    $refund->id,
                    $refund->order_id
                ),
                $logContext
            );

            $this->orderRepository->commitTransaction();

            return true;
        } catch (\Exception $exception) {
            $this->logger->critical(
                $exception->getMessage(),
                $logContext
            );

            $this->refundRepository->logError(
                $refund->id,
                $exception->getMessage(),
                Refund::RECOVERABLE_STAGE_TEXTRAIL_ISSUE
            );

            $exception = new RefundFailureException(sprintf(
                'The refund {%d} for {%d} order had a critical error when it was tried to be rejected: %s',
                $refund->id,
                $refund->order_id,
                $exception->getMessage()
            ));

            throw $exception->withContext($logContext)->withTextrailRma($refund->textrail_rma);
        }
    }

    /**
     * @param Refund $refund
     * @param array<array{sku:string, title:string, id:int, amount: float, qty: int, price: float}> $requestedParts
     * @param array<array{sku:string, title:string, id:int, amount: float, qty: int, price: float}> $authorizedParts
     * @return bool
     */
    private function markAsAuthorized(Refund $refund, array $requestedParts, array $authorizedParts): bool
    {
        $logContext = ['id' => $refund->id, 'rma' => $refund->textrail_rma];

        try {
            $this->orderRepository->beginTransaction();

            $this->refundRepository->markAsAuthorized($refund, $requestedParts, $authorizedParts);

            $this->orderService->updateRefundSummary($refund->order_id);

            $this->logger->info(
                sprintf(
                    'The refund {%d} for {%d} order was successfully marked as authorized',
                    $refund->id,
                    $refund->order_id
                ),
                $logContext
            );

            $this->orderRepository->commitTransaction();

            return true;
        } catch (\Exception $exception) {
            $this->logger->critical(
                $exception->getMessage(),
                $logContext
            );

            $this->refundRepository->logError(
                $refund->id,
                $exception->getMessage(),
                Refund::RECOVERABLE_STAGE_TEXTRAIL_ISSUE
            );

            $exception = new RefundFailureException(sprintf(
                'The refund {%d} for {%d} order had a critical error when it was tried to be authorized: %s',
                $refund->id,
                $refund->order_id,
                $exception->getMessage()
            ));

            throw $exception->withContext($logContext)->withTextrailRma($refund->textrail_rma);
        }
    }

    /**
     * @param Refund $refund
     * @param array<array{sku:string, title:string, id:int, amount: float, qty: int, price: float}> $parts
     * @return bool
     */
    private function markAsReturnReceived(Refund $refund, array $parts): bool
    {
        $logContext = ['id' => $refund->id, 'rma' => $refund->textrail_rma];

        try {
            $this->orderRepository->beginTransaction();

            $this->refundRepository->markAsReturnReceived($refund, $parts);

            $this->orderService->updateRefundSummary($refund->order_id);

            $this->logger->info(
                sprintf(
                    'The refund {%d} for {%d} order was successfully marked as return received',
                    $refund->id,
                    $refund->order_id
                ),
                $logContext
            );

            // When a refund has reached the status 'Return received', it should be processed on the payment gateway
            $this->dispatchPaymentGatewayRefundJob($refund);

            $this->orderRepository->commitTransaction();

            return true;
        } catch (\Exception $exception) {
            $this->logger->critical(
                $exception->getMessage(),
                $logContext
            );

            $this->refundRepository->logError(
                $refund->id,
                $exception->getMessage(),
                Refund::RECOVERABLE_STAGE_TEXTRAIL_ISSUE
            );

            $exception = new RefundFailureException(sprintf(
                'The refund {%d} for {%d} order had a critical error when it was tried to be return received: %s',
                $refund->id,
                $refund->order_id,
                $exception->getMessage()
            ));

            throw $exception->withContext($logContext)->withTextrailRma($refund->textrail_rma);
        }
    }

    /**
     * @param array<array{sku:string, title:string, id:int, amount: float, qty: int, price: float}> $originalParts
     * @param array<array{sku: string, qty: int}> $parts array of parts indexed by part sku
     * @return array{originalParts: array, updatedParts: array, partsAmount: Money}
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    private function reCalculateParts(array $originalParts, array $parts): array
    {
        // Given Textrail might send different quantities, so we need to update those requested quantities
        // and the parts amount

        $updatedParts = [];

        $partsAmount = Money::zero('USD');

        foreach ($parts as $sku => $part) {
            $qty = $part['qty'];

            if (!isset($originalParts[$sku])) {
                throw new RefundException(sprintf('"%s" part was not originally requested to be refunded', $sku));
            }

            $originalPart = $originalParts[$sku];

            if ($qty < 0 || $qty > $originalPart['qty']) {
                throw new RefundException(sprintf('"%s" part must be in the range of 0 and %d', $sku, $originalPart['qty']));
            }

            $updatedParts[] = array_merge($originalPart, ['qty' => $qty]);
            $partsAmount = $partsAmount->plus($qty * $originalPart['price']);
        }

        return ['originalParts' => $originalParts, 'updatedParts' => $updatedParts, 'partsAmount' => $partsAmount];
    }
}
