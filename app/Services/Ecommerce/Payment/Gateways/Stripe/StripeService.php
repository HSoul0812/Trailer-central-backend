<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\Payment\Gateways\Stripe;

use App\Exceptions\Ecommerce\RefundPaymentGatewayException;
use App\Services\Ecommerce\Payment\Gateways\PaymentGatewayServiceInterface;
use Brick\Money\Money;
use Illuminate\Support\Facades\Config;
use Stripe\StripeClient;

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

    public function __construct()
    {
        $this->client = $this->getClient();
    }

    /**
     * @param  string  $objectId
     * @param  Money  $amount
     * @param  array{sku:string, title:string}  $parts
     *
     * @param  string|null  $reason
     * @return StripeRefundResultInterface
     * @throws RefundPaymentGatewayException when there was some error on the stripe remote process
     */
    public function refund(
        string $objectId,
        Money $amount,
        array $parts = [],
        ?string $reason = null
    ): StripeRefundResultInterface {
        $request = [
            'payment_intent' => $objectId,
            // Stripe doesn't allow decimals, instead it required the amount in cents
            'amount' => $amount->getAmount()->withPointMovedRight(2)->getUnscaledValue()
        ];

        if (!empty($parts)) {
            $request['metadata'] = ['parts' => $parts];
        }

        if (!empty($reason) && $this->isValidRefundReason($reason)) {
            $request['reason'] = $reason;
        }

        try {
            $refund = $this->client->refunds->create($request);

            return StripeRefundResult::from($refund->toArray(), false);
        } catch (\Exception $exception) {
            throw new RefundPaymentGatewayException($exception->getMessage());
        }
    }

    protected function getClient(): StripeClient
    {
        if (!$this->client) {
            $this->client = new StripeClient(Config::get('stripe_checkout.secret'));
        }

        return $this->client;
    }

    protected function isValidRefundReason(string $reason): bool
    {
        return in_array($reason, self::REFUND_REASONS);
    }
}
