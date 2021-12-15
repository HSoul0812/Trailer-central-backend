<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace App\Models\Ecommerce\CompletedOrder;

use App\Contracts\Support\DTO;
use App\Models\Parts\Textrail\RefundedPart;
use App\Repositories\Ecommerce\RefundRepositoryInterface;
use App\Traits\WithGetter;
use Brick\Math\RoundingMode;
use Brick\Money\Money;

/**
 * @property-read int $orderId
 *
 * @property-read Money $total
 * @property-read Money $tax
 * @property-read Money $handlingFee
 * @property-read Money $shippingFee
 * @property-read Money $totalPartsAmount
 * @property-read int[] $partsQtys
 *
 * @property-read Money $totalRefunded
 * @property-read Money $taxRefunded
 * @property-read Money $shippingRefunded
 * @property-read Money $adjustmentRefunded
 * @property-read Money $partsRefundedAmount
 * @property-read int[] $partsRefundedQtys
 *
 * @property-read Money $totalRemainingBalance
 * @property-read Money $partsRemainingBalance
 * @property-read Money $handlingRemainingBalance
 * @property-read Money $shippingRemainingBalance
 * @property-read Money $taxRemainingBalance
 */
final class OrderAmountsBag implements DTO
{
    use WithGetter;

    /** @var int */
    private $orderId;

    /** @var Money */
    private $total;

    /** @var Money */
    private $tax;

    /** @var Money */
    private $handlingFee;

    /** @var Money */
    private $shippingFee;

    /** @var Money */
    private $totalPartsAmount;

    /** @var array<int> */
    private $partsQtys;

    /** @var Money */
    private $totalRefunded;

    /** @var Money */
    private $taxRefunded;

    /** @var Money */
    private $handlingRefunded;

    /** @var Money */
    private $shippingRefunded;

    /** @var Money */
    private $adjustmentRefunded;

    /** @var Money */
    private $partsRefundedAmount;

    /** @var array<int> */
    private $partsRefundedQtys;

    /** @var Money */
    private $totalRemainingBalance;

    /** @var Money */
    private $partsRemainingBalance;

    /** @var Money */
    private $handlingRemainingBalance;

    /** @var Money */
    private $shippingRemainingBalance;

    /** @var Money */
    private $taxRemainingBalance;

    private function __construct(CompletedOrder $order)
    {
        $this->orderId = $order->id;

        $this->total = Money::of((float)$order->total_amount, 'USD', null, RoundingMode::HALF_UP);
        $this->tax = Money::of((float)$order->tax, 'USD', null, RoundingMode::HALF_UP);
        $this->handlingFee = Money::of((float)$order->handling_fee, 'USD', null, RoundingMode::HALF_UP);
        $this->shippingFee = Money::of((float)$order->shipping_fee, 'USD', null, RoundingMode::HALF_UP);
        $this->totalPartsAmount = Money::zero('USD');

        $this->partsQtys = collect($order->parts)->keyBy('id')->map(function ($part) {
            $this->totalPartsAmount = $this->totalPartsAmount->plus($part['qty'] * $part['price'], RoundingMode::HALF_UP);

            return $part['qty'];
        })->toArray();

        $this->totalRefunded = Money::of((float)$order->total_refunded_amount, 'USD', null, RoundingMode::HALF_UP);
        $this->taxRefunded = Money::of((float)$order->tax_refunded_amount, 'USD', null, RoundingMode::HALF_UP);
        $this->handlingRefunded = Money::of((float)$order->handling_refunded_amount, 'USD', null, RoundingMode::HALF_UP);
        $this->shippingRefunded = Money::of((float)$order->shipping_refunded_amount, 'USD', null, RoundingMode::HALF_UP);
        $this->adjustmentRefunded = Money::of((float)$order->adjustment_refunded_amount, 'USD', null, RoundingMode::HALF_UP);
        $this->partsRefundedAmount = Money::of((float)$order->parts_refunded_amount, 'USD', null, RoundingMode::HALF_UP);

        $this->partsRefundedQtys = $this->getRefundRepository()->getRefundedParts($order->id)->keyBy('id')->map(function (RefundedPart $part) {
            return $part->qty;
        })->toArray();

        $this->totalRemainingBalance = $this->total->minus($this->totalRefunded);
        $this->partsRemainingBalance = $this->totalPartsAmount->minus($this->partsRefundedAmount);
        $this->handlingRemainingBalance = $this->handlingFee->minus($this->handlingRefunded);
        $this->shippingRemainingBalance = $this->shippingFee->minus($this->shippingRefunded);
        $this->taxRemainingBalance = $this->tax->minus($this->taxRefunded);
    }

    public static function fromOrder(CompletedOrder $order): self
    {
        return new OrderAmountsBag($order);
    }

    public function asArray(): array
    {
        return [
            'order_id' => $this->orderId,

            'total' => $this->total->getAmount(),
            'tax' => $this->tax->getAmount(),
            'handling_fee' => $this->handlingFee->getAmount(),
            'shipping_fee' => $this->shippingFee->getAmount(),
            'total_parts_amount' => $this->totalPartsAmount->getAmount(),
            'parts_qtys' => $this->partsQtys,

            'total_refunded' => $this->totalRefunded->getAmount(),
            'tax_refunded' => $this->taxRefunded->getAmount(),
            'shipping_refunded' => $this->shippingRefunded->getAmount(),
            'handling_refunded' => $this->handlingRefunded->getAmount(),
            'adjustment_refunded' => $this->adjustmentRefunded->getAmount(),
            'parts_refunded_amount' => $this->partsRefundedAmount->getAmount(),
            'parts_refunded_qtys' => $this->partsRefundedQtys,

            'total_remaining_balance' => $this->totalRemainingBalance->getAmount(),
            'parts_remaining_balance' => $this->partsRemainingBalance->getAmount(),
            'handling_remaining_balance' => $this->handlingRemainingBalance->getAmount(),
            'shipping_remaining_balance' => $this->shippingRemainingBalance->getAmount(),
            'tax_remaining_balance' => $this->shippingRemainingBalance->getAmount(),
        ];
    }

    private function getRefundRepository(): RefundRepositoryInterface
    {
        return app(RefundRepositoryInterface::class);
    }
}
