<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace App\Services\Ecommerce\Refund;

use App\Contracts\Support\DTO;
use App\Exceptions\Ecommerce\RefundAmountException;
use App\Exceptions\Ecommerce\RefundException;
use App\Http\Requests\Ecommerce\IssueRefundOrderRequest;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Models\Ecommerce\CompletedOrder\OrderAmountsBag;
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
 * @property-read  array<array{order_item_id: int, qty: int}> $textrailItems
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

    /** @var array<array{order_item_id: int, qty: int}> */
    private $textrailItems;

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
        ['total' => $partsAmount, 'list' => $parts] = $this->getSummaryParts($partsWithQtys, $orderId);

        $this->parts = $parts;

        // prepare the refund info to match with the order info in Textrail side
        $this->textrailItems = collect($parts)->filter(function (array $part): bool {
            return $part['textrail'] !== null;
        })->map(function (array $part) {
            return [
                'order_item_id' => $part['textrail']['item_id'],
                'qty' => $part['qty']
            ];
        })->toArray();

        $this->partsAmount = $partsAmount;
        $this->adjustmentAmount = $adjustmentAmount;
        $this->handlingAmount = $handlingAmount;
        $this->shippingAmount = $shippingAmount;
        $this->taxAmount = $taxAmount;
        $this->reason = $reason;
        $this->totalAmount = $partsAmount->plus($adjustmentAmount)
            ->plus($handlingAmount)
            ->plus($shippingAmount)
            ->plus($taxAmount);
    }

    public static function fromRequest(IssueRefundOrderRequest $request): self
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
            'textrail_items' => $this->textrailItems,
            'parts_amount' => $this->partsAmount->getAmount()->toFloat(),
            'adjustment_amount' => $this->adjustmentAmount->getAmount()->toFloat(),
            'handling_amount' => $this->handlingAmount->getAmount()->toFloat(),
            'shipping_amount' => $this->shippingAmount->getAmount()->toFloat(),
            'tax_amount' => $this->taxAmount->getAmount()->toFloat(),
            'total_amount' => $this->totalAmount->getAmount()->toFloat(),
            'reason' => $this->reason
        ];
    }

    /**
     * @param array<array{qty: int, id: int}> $parts parts qtys indexed by part id
     * @return array{total: Money, list:array{sku:string, title:string, id:int, amount: float, qty: int, price: float}}
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException when the order is not found
     */
    private function getSummaryParts(array $parts, int $orderId): array
    {
        $this->order = $this->getOrderRepository()->get(['id' => $orderId]);

        $indexedOrderParts = collect($this->order->parts)->keyBy('id')->toArray();

        $indexedOrderTextrailItems = collect($this->order->ecommerce_items ?? [])->keyBy('sku')
            ->map(static function (array $item): array {
                return [
                    'item_id' => (int)$item['item_id'],
                    'product_type' => $item['product_type'],
                    'quote_id' => (int)$item['quote_id']
                ];
            })->toArray();

        $partModelsToBeRefunded = $this->getRefundRepository()->getPartsToBeRefunded(array_keys($parts));

        // calculates the parts total having in count the purchase price, not the current part price
        $total = $partModelsToBeRefunded->reduce(function (Money $carry, Part $part) use ($parts, $indexedOrderParts): Money {
            // when the part is found in the order, the purchase price is used, otherwise the current price is used
            // but only to be able showing a proper error message
            $price = (float)(isset($indexedOrderParts[$part->id]) ? $indexedOrderParts[$part->id]['price'] : $part->price);

            return $carry->plus($price * $parts[$part->id]['qty']);
        }, Money::zero('USD'));

        $list = $partModelsToBeRefunded->map(static function (Part $part) use ($parts, $indexedOrderParts, $indexedOrderTextrailItems): array {
            $price = (float)(isset($indexedOrderParts[$part->id]) ? $indexedOrderParts[$part->id]['price'] : $part->price);
            $textTrailInfo = $indexedOrderTextrailItems[$part->sku] ?? null;

            return [
                'sku' => $part->sku,
                'title' => $part->title,
                'id' => $part->id,
                'price' => $price,
                'amount' => $price * $parts[$part->id]['qty'],
                'qty' => $parts[$part->id]['qty'],
                'textrail' => $textTrailInfo
            ];
        })->toArray();

        return ['total' => $total, 'list' => $list];
    }

    /**
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
     */
    public function validate(): void
    {
        if (!$this->order->isPaid()) {
            throw new RefundException(sprintf('%d order is not refundable due it is unpaid', $this->order->id), 'order');
        }

        if (!$this->order->isRefundable()) {
            throw new RefundException(sprintf('%d order is not refundable due it is refunded', $this->order->id), 'order');
        }

        if (empty($this->order->payment_intent)) {
            throw new RefundException(
                sprintf('%d order is not refundable due it has not a payment unique id', $this->order->id),
                'order'
            );
        }

        $orderAmounts = OrderAmountsBag::fromOrder($this->order);

        if ($orderAmounts->partsQtys === [] && $this->parts !== []) {
            throw new RefundException(
                sprintf(
                    '%d order cannot be refunded due it has not a related parts matching with the request',
                    $this->order->id
                ),
                'parts'
            );
        }

        foreach ($this->parts as $part) {
            ['id' => $partId, 'qty' => $partQty] = $part;

            if (array_key_exists($partId, $orderAmounts->partsQtys)) {
                // check if the refunded qty will be greater than the total qty for the part item
                if (isset($orderAmounts->partsRefundedQtys[$partId])
                    && ($orderAmounts->partsRefundedQtys[$partId] + $partQty) > $orderAmounts->partsQtys[$partId]
                ) {
                    throw new RefundAmountException(
                        sprintf(
                            'The refund part[%d] qty(%d) is greater than the remaining qty(%d)',
                            $partId,
                            $partQty,
                            $orderAmounts->partsQtys[$partId] - $orderAmounts->partsRefundedQtys[$partId]
                        ),
                        'parts'
                    );
                } elseif (!isset($orderAmounts->partsRefundedQtys[$partId]) && $partQty > $orderAmounts->partsQtys[$partId]) {
                    throw new RefundAmountException(
                        sprintf(
                            'The refund part[%d] qty(%d) is greater than the purchase qty(%d)',
                            $partId,
                            $partQty,
                            $orderAmounts->partsQtys[$partId]
                        ),
                        'parts'
                    );
                }
            } else {
                throw new RefundException(sprintf('The refund part[%d] is not a placed part', $partId), 'parts');
            }
        }

        // check if the amounts are greater than the order remaining balances
        // when it check the total amount, it will check implicitly the adjustment amount
        foreach (['total', 'parts', 'handling', 'shipping', 'tax'] as $amountToCheck) {
            if ($orderAmounts->{$amountToCheck . 'RemainingBalance'}
                ->minus($this->{$amountToCheck . 'Amount'})
                ->isLessThan(0)) {
                throw new RefundAmountException(
                    sprintf(
                        'The refund %s amount $%0.2f is not valid due it is greater than order remaining %s balance $%0.2f',
                        $amountToCheck,
                        $this->{$amountToCheck . 'Amount'}->getAmount()->toFloat(),
                        $amountToCheck,
                        $orderAmounts->{$amountToCheck . 'RemainingBalance'}->getAmount()->toFloat()
                    ),
                    $amountToCheck
                );
            }
        }
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
