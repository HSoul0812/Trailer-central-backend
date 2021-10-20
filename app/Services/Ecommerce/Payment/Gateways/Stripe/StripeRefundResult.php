<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\Payment\Gateways\Stripe;

use App\Traits\WithFactory;
use App\Traits\WithGetter;

/**
 * @see https://stripe.com/docs/api/refunds/object
 */
class StripeRefundResult implements StripeRefundResultInterface
{
    use WithGetter;
    use WithFactory;

    /** @var string */
    private $id;

    /** @var string */
    private $balance_transaction;

    /** @var string */
    private $charge;

    /** @var array */
    private $metadata;

    /** @var string */
    private $receipt_number;

    /** @var string */
    private $status;

    /** @var string */

    public function getId(): string
    {
        return $this->id;
    }

    public function getOriginalStatus(): string
    {
        return $this->status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getReceiptNumber(): string
    {
        return $this->receipt_number;
    }

    public function getMetaData(): array
    {
        return $this->metadata;
    }

    public function getBalanceTransaction(): string
    {
        return $this->balance_transaction;
    }

    public function getCharge(): string
    {
        return $this->charge;
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'balance_transaction' => $this->balance_transaction,
            'charge' => $this->charge,
            'receipt_number' => $this->receipt_number,
            'metadata' => $this->metadata
        ];
    }
}
