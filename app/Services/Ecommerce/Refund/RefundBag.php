<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace App\Services\Ecommerce\Refund;

use App\Contracts\Support\DTO;
use App\Http\Requests\Ecommerce\RequestRefundOrderRequest;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Models\Parts\Textrail\Part;
use App\Repositories\Ecommerce\CompletedOrderRepositoryInterface;
use App\Repositories\Ecommerce\RefundRepositoryInterface;
use App\Traits\WithGetter;
use Brick\Money\Money;

/**
 * @property-read  CompletedOrder $order
 * @property-read  Money $partsAmount
 * @property-read  Money $adjustmentAmount
 * @property-read  Money $handlingAmount
 * @property-read  Money $shippingAmount
 * @property-read  Money $taxAmount
 * @property-read  Money $totalAmount
 * @property-read  array<array{id: int, qty: int, price: float, amount: float}> $parts array indexed by part id containing the purchase price and its amount
 * @property-read  string|null $reason
 */
final class RefundBag implements DTO
{
    use WithGetter;

    /** @var Money */
    private $partsAmount;

    /** @var Money */
    private $adjustmentAmount;

    /** @var Money */
    private $handlingAmount;

    /** @var Money */
    private $shippingAmount;

    /** @var Money */
    private $taxAmount;

    /** @var Money */
    private $totalAmount;

    /** @var string|null */
    private $reason;

    /** @var array<array{id: int, qty: int, price: float, amount: float}> array indexed by part id containing the purchase price and its amount */
    private $parts;

    /** @var CompletedOrder */
    private $order;

    /**
     * @param int $orderId
     * @param array<array{id: int, qty: int}> $partsWithQtys array indexed by part id containing the qty of every part
     * @param Money $adjustmentAmount
     * @param Money $handlingAmount
     * @param Money $shippingAmount
     * @param Money $taxAmount
     * @param string|null $reason
     */
    public function __construct(
        int     $orderId,
        array   $partsWithQtys,
        Money   $adjustmentAmount,
        Money   $handlingAmount,
        Money   $shippingAmount,
        Money   $taxAmount,
        ?string $reason = null
    )
    {
        ['total' => $partsAmount, 'list' => $parts] = $this->getSummaryParts(array_keys($partsWithQtys), $orderId);

        $this->parts = $parts;
        $this->partsAmount = $partsAmount;
        $this->adjustmentAmount = $adjustmentAmount;
        $this->handlingAmount = $handlingAmount;
        $this->shippingAmount = $shippingAmount;
        $this->taxAmount = $taxAmount;
        $this->reason = $reason;
        $this->totalAmount = $partsAmount->plus($adjustmentAmount)
            ->plus($adjustmentAmount)
            ->plus($handlingAmount)
            ->plus($shippingAmount)
            ->plus($taxAmount);
    }

    public function fromRequest(RequestRefundOrderRequest $request): self
    {
        return new self(
            $request->orderId(),
            $request->parts(),
            $request->adjustmentAmount(),
            $request->handlingAmount(),
            $request->shippingAmount(),
            $request->taxAmount(),
            $request->reason
        );
    }

    public function asArray(): array
    {
        return [
            'order_id' => $this->order->id,
            'parts' => $this->parts,
            'parts_amount' => $this->partsAmount->getAmount(),
            'adjustment_amount' => $this->adjustmentAmount->getAmount(),
            'handling_amount' => $this->handlingAmount->getAmount(),
            'shipping_amount' => $this->shippingAmount->getAmount(),
            'tax_amount' => $this->taxAmount->getAmount(),
            'total_amount' => $this->totalAmount->getAmount(),
            'reason' => $this->reason
        ];
    }

    /**
     * @param array{int} $parts parts ids
     * @return array{total: Money, list:array{sku:string, title:string, id:int, amount: float, qty: int, price: float}}
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException when the order is not found
     */
    private function getSummaryParts(array $parts, int $orderId): array
    {
        $this->order = $this->getOrderRepository()->get(['id' => $orderId]);

        $indexedOrderParts = collect($this->order->parts)->keyBy('id')->toArray();

        $partModelsToBeRefunded = $this->getRefundRepository()->getPartsToBeRefunded(array_keys($parts));

        // calculates the parts total having in count the purchase price, not the current part price
        $total = $partModelsToBeRefunded->reduce(function (Money $carry, Part $part) use ($parts, $indexedOrderParts): Money {
            return $carry->plus($indexedOrderParts[$part->id]['price'] * $parts[$part->id]->qty);
        }, Money::zero('USD'));

        $list = $partModelsToBeRefunded->map(static function (Part $part) use ($parts, $indexedOrderParts): array {
            return [
                'sku' => $part->sku,
                'title' => $part->title,
                'id' => $part->id,
                'price' => $indexedOrderParts[$part->id]['price'],
                'amount' => $indexedOrderParts[$part->id]['price'] * $parts[$part->id]['qty'],
                'qty' => $parts[$part->id]['qty']
            ];
        })->toArray();

        return ['total' => $total, 'list' => $list];
    }

    private function getRefundRepository(): RefundRepositoryInterface
    {
        return app(RefundRepositoryInterface::class);
    }

    private function getOrderRepository(): CompletedOrderRepositoryInterface
    {
        return app(CompletedOrderRepositoryInterface::class);
    }
}
