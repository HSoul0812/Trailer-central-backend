<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\Payment\Gateways\Stripe;

use App\Exceptions\Ecommerce\RefundPaymentGatewayException;
use App\Exceptions\Ecommerce\TextrailSyncException;
use App\Services\Ecommerce\Payment\Gateways\PaymentGatewayServiceInterface;
use Brick\Money\Money;
use GuzzleHttp\Exception\ClientException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Stripe\StripeClientInterface;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;

class StripeService implements PaymentGatewayServiceInterface
{
    /** @var StripeClient */
    private $client;

    /**
     * @array given in the future the common reasons could be changed, we need to ensure those reasons are according to Stripe API
     */
    public const REFUND_REASONS = [
        'duplicate',
        'fraudulent',
        'requested_by_customer'
    ];

    public function __construct(StripeClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param  string  $objectId
     * @param  Money  $amount
     * @param  array<array{sku:string, title:string, id:int, amount: float}> $parts
     * @param  string|null  $reason
     * @return StripePaymentGatewayRefundResultInterface
     * @throws RefundPaymentGatewayException when there was some error calling the stripe remote process
     */
    public function refund(
        string $objectId,
        Money $amount,
        array $parts = [],
        ?string $reason = null
    ): StripePaymentGatewayRefundResultInterface {
        $request = [
            'payment_intent' => $objectId,
            // Stripe doesn't allow decimals, instead it required the amount in cents
            'amount' => $amount->getAmount()->withPointMovedRight(2)->getUnscaledValue()
        ];

        if (!empty($parts)) {
            $request['metadata'] = ['parts' => json_encode($parts)];
        }

        if (!empty($reason) && $this->isValidRefundReason($reason)) {
            $request['reason'] = $reason;
        }

        try {
            $refund = $this->client->refunds->create($request);

            return StripeRefundResult::from($refund->toArray(), false);
        } catch (ApiErrorException $exception) {
            throw RefundPaymentGatewayException::factory(
                $exception->getMessage(),
                $exception->getHttpStatus(),
                $exception->getHttpBody(),
                $exception->getJsonBody(),
                (array)$exception->getHttpHeaders(),
                $exception->getStripeCode()
            );
        } catch (\Exception $exception) {
            throw new RefundPaymentGatewayException($exception->getMessage());
        }
    }

    protected function isValidRefundReason(string $reason): bool
    {
        return in_array($reason, self::REFUND_REASONS);
    }

    public function getInvoice(CompletedOrder $completedOrder): array
    {
      $invoice = $this->client->invoices->retrieve($completedOrder->invoice_id);

      return $invoice->toArray();
    }

    public function updatePaymentIntent(array $params): bool
    {
        try {
            $this->client->paymentIntents->update($params['payment_intent'], [
                'metadata' => [
                    'order_id' => $params['ecommerce_order_id']
                ]
            ]);

            return true;
        } catch (ClientException | \Exception $exception) {
            throw new TextrailSyncException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function confirmPaymentIntent(array $params): bool
    {
        try {
            $this->client->paymentIntents->confirm($params['payment_intent']);

            return true;
        } catch (ClientException | \Exception $exception) {
            throw new TextrailSyncException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
