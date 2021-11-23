<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\Refund;

use App\Contracts\LoggerServiceInterface;
use App\Exceptions\Ecommerce\AfterRemoteRefundException;
use App\Exceptions\Ecommerce\RefundAmountException;
use App\Exceptions\Ecommerce\RefundException;
use App\Exceptions\Ecommerce\RefundHttpClientException;
use App\Exceptions\Ecommerce\RefundPaymentGatewayException;
use App\Exceptions\NotImplementedException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
     * @throws RefundException when the order has not a payment unique id
     * @throws RefundException when the order has not a related parts matching with the request
     * @throws RefundAmountException when the refund total amount is greater than the order remaining total balance
     * @throws RefundAmountException when the refund parts amount is greater than the order remaining parts balance
     * @throws RefundAmountException when the refund handling amount is greater than the order remaining handling balance
     * @throws RefundAmountException when the refund shipping amount is greater than the order remaining shipping balance
     * @throws RefundAmountException when the refund tax amount is greater than the order remaining tax balance
     * @throws RefundAmountException when the some provided part qty is greater than the remaining qty
     * @throws RefundAmountException when the some provided part qty is greater than the purchase qty
     * @throws RefundException when a provided part was not a placed part in the order
     * @throws \Exception when some error has occurred on DB saving time
     * @throws \Exception when some error has occurred trying to send the refund request to TexTrail
     * @throws RefundException when there was some error on payment TexTrail local process
     * @throws RefundHttpClientException when there was some error on Textrail remote process
     * @throws AfterRemoteRefundException when there was some error after the refund was successfully received by Textrail
     */
    public function issue(RefundBag $refundBag): Refund
    {
        $refundBag->validate();

        // Some issuable context information
        $logContext = $refundBag->asArray();

        // This logic must not be a transaction to be able recuperating from an error after the successfully received Textrail refund request
        $refund = $this->createRefund($refundBag);

        /** @var int $textrailRefundId */

        try {
            $textrailRefundId = $this->textrailService->issueRefund($refundBag);

            $this->updateRefundAfterTextrailSuccess($refund, $textrailRefundId);

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

            $this->refundRepository->markAsFailed(
                $refund, $message,
                Refund::ERROR_STAGE_TEXTRAIL_ISSUE_REMOTE
            );

            throw $exception;
        } catch (AfterRemoteRefundException $exception) {
            $this->logger->critical(
                $exception->getMessage(),
                array_merge($logContext, ['textrail_id' => $textrailRefundId])
            );

            // This is a naive approach given that if there was a failure when it tried to finish the refund (post-gateway),
            // then, it probably will fail again here, but at least the critical log was recorded, and the subsequent
            // refund attempt will be prevented
            $this->refundRepository->markAsRecoverableFailure(
                $refund,
                ['textrail_id' => $textrailRefundId],
                $exception->getMessage(),
                Refund::RECOVERABLE_STAGE_TEXTRAIL_ISSUE
            );

            throw $exception;
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), $logContext);

            $this->refundRepository->markAsFailed(
                $refund, $exception->getMessage(),
                Refund::ERROR_STAGE_TEXTRAIL_ISSUE_LOCAL
            );

            throw $exception;
        }
    }

    /**
     * It will create a full refund in the database, then it will enqueue a refund process on the payment gateway.
     *
     * @param int $orderId
     * @return Refund
     */
    public function makeReturn(int $orderId): Refund
    {
        throw new NotImplementedException;
    }

    /**
     * It will call the refund process on the payment gateway.
     *
     * @param int $refundId
     * @return PaymentGatewayRefundResultInterface
     * @throws RefundPaymentGatewayException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function refund(int $refundId): PaymentGatewayRefundResultInterface
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

                return $gatewayRefundResponse;
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
     * @param int $textrailRefundId
     *
     * @throws AfterRemoteRefundException when there was some error after the refund was successfully received by Textrail
     */
    private function updateRefundAfterTextrailSuccess(Refund $refund, int $textrailRefundId): void
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

            $exception = new AfterRemoteRefundException(sprintf(
                'The refund {%d} for {%d} order had a critical error after the TexTrail remote process: %s',
                $refund->id,
                $refund->order_id,
                $exception->getMessage()
            ));

            // just in case any error has occurred after a successful gateway refund, we need to store that
            // response payload somewhere for monitoring purpose, or any refund issue
            // @todo: many payment gateways doesn't allow cancelling refunds, so this should be a charge to rollback the refund

            throw $exception->withContext($logContext)->withTextrailId($textrailRefundId);
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
}
