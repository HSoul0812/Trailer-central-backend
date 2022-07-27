<?php

declare(strict_types=1);

namespace App\Repositories\Ecommerce;

use App\Models\Ecommerce\Refund;
use App\Models\Parts\Textrail\Part;
use App\Models\Parts\Textrail\RefundedPart;
use App\Services\Ecommerce\Payment\Gateways\PaymentGatewayRefundResultInterface;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RefundRepository implements RefundRepositoryInterface
{
    public function create(array $params): Refund
    {
        return Refund::create($params)->refresh();
    }

    public function get(int $refundId): ?Refund
    {
        return Refund::find($refundId);
    }

    public function getByRma(int $rma): ?Refund
    {
        return Refund::query()->where('textrail_rma', $rma)->first();
    }

    /**
     * @param array $params the filterable parameters are "dealer_id" and "order_id"
     * @return array<Refund>|Collection|LengthAwarePaginator
     * @throws \InvalidArgumentException when "order_id" and "dealer_id" arguments were not provided
     */
    public function getAll(array $params)
    {
        $query = Refund::with('order');

        if (empty($params['dealer_id']) && empty($params['order_id'])) {
            throw new \InvalidArgumentException('RefundRepository::getAll requires at least one argument of: "dealer_id" or "order_id" to filter by');
        }

        if (!empty($params['dealer_id'])) {
            $query->where('ecommerce_order_refunds.dealer_id', '=', $params['dealer_id']);
        }

        if (!empty($params['order_id'])) {
            $query->where('order_id', '=', $params['order_id']);
        }

        $query->where('ecommerce_order_refunds.status', '!=', Refund::STATUS_FAILED);

        if (isset($params[self::CONDITION_AND_WHERE_IN]) && is_array($params[self::CONDITION_AND_WHERE_IN])) {
            foreach ($params[self::CONDITION_AND_WHERE_IN] as $field => $values) {
                $query->whereIn($field, $values);
            }
        }

        if (isset($params[self::CONDITION_AND_WHERE_NOT_IN]) && is_array($params[self::CONDITION_AND_WHERE_NOT_IN])) {
            foreach ($params[self::CONDITION_AND_WHERE_NOT_IN] as $field => $values) {
                $query->whereNotIn($field, $values);
            }
        }

        if (!empty($params['paged'])) {
            if (empty($params['per_page'])) {
                $params['per_page'] = 100;
            }

            return $query->paginate($params['per_page'])->appends($params);
        }

        return $query->get();
    }

    /**
     * @param int $orderId
     * @return array<RefundedPart>|Collection
     */
    public function getRefundedParts(int $orderId): Collection
    {
        $partsAmount = [];
        $partsQty = [];
        $partsStatus = [];

        $amountAdder = static function (string $sku, float $amount) use (&$partsAmount) {
            return isset($partsAmount[$sku]) ? $partsAmount[$sku] + $amount : $amount;
        };

        $qtyAdder = static function (string $sku, int $qty) use (&$partsQty) {
            return isset($partsQty[$sku]) ? $partsQty[$sku] + $qty : $qty;
        };

        // we'll make two arrays of parts with their total refunded amount a qty indexed by part id
        $this->getAll([
            'order_id' => $orderId,
            self::CONDITION_AND_WHERE_NOT_IN => [
                'status' => [Refund::STATUS_FAILED, Refund::STATUS_DENIED]
            ]
        ])->each(static function (Refund $refund) use (&$partsAmount, &$partsQty, &$partsStatus, $amountAdder, $qtyAdder) {
            $orderParts = $refund->order->ecommerce_items;
            foreach ($refund->parts as $part) {
                $partsAmount[$part['sku']] = $amountAdder($part['sku'], $part['amount']);
                $partsQty[$part['sku']] = $qtyAdder($part['sku'], (int)$part['qty']);
            }

            foreach ($orderParts as $orderPart) {
               if (array_key_exists($orderPart['sku'], $partsQty)) {
                   $refundQty = $partsQty[$orderPart['sku']];
                   $totalQty = $orderPart['qty'];

                   if ($totalQty > $refundQty) {
                       $partsStatus[$orderPart['sku']] = RefundedPart::PARTY_REFUND;
                   }

                   if ($totalQty <= $refundQty) {
                       $partsStatus[$orderPart['sku']] = RefundedPart::FULLY_REFUND;
                   }
               }
            }
        });

        return Part::query()
            ->whereIn('sku', array_keys($partsAmount))
            ->get()
            ->map(static function (Part $part) use ($partsAmount, $partsQty, $partsStatus): RefundedPart {
                return RefundedPart::from([
                    'id' => $part->id,
                    'title' => $part->title,
                    'sku' => $part->sku,
                    'amount' => $partsAmount[$part->sku],
                    'qty' => $partsQty[$part->sku],
                    'status' => $partsStatus[$part->sku] ?? RefundedPart::NON_REFUND,
                ]);
            });
    }

    /**
     * @param array<int> $parts
     * @return array<Part>|Collection
     */
    public function getPartsToBeRefunded(array $parts): Collection
    {
        $partModels = Part::query()->whereIn('id', array_unique($parts))->get()->keyBy('id');

        $createDummyPart = static function (int $id): Part {
            $part = new Part();
            $part->id = $id;

            return $part;
        };

        foreach ($parts as $partId) {
            if (!isset($partModels[$partId])) {
                $partModels[$partId] = $createDummyPart($partId);
            }
        }

        return $partModels;
    }

    public function getRefundedAmount(int $orderId): Money
    {
        $amount = (float)Refund::query()
            ->where('order_id', '=', $orderId)
            ->whereNotIn('status', [Refund::STATUS_FAILED, Refund::STATUS_DENIED])
            ->sum('total_amount');

        return Money::of($amount, 'USD', null, RoundingMode::HALF_DOWN);
    }

    /**
     * @param int $orderId
     * @return array{total_amount: Money, parts_amount: Money, handling_amount: Money, shipping_amount: Money, adjustment_amount: Money, tax_amount: Money}
     */
    public function getOrderRefundSummary(int $orderId): array
    {
        $select = DB::raw('
                SUM(parts_amount) as parts_amount,
                SUM(handling_amount) as handling_amount,
                SUM(shipping_amount) as shipping_amount,
                SUM(adjustment_amount) as adjustment_amount,
                SUM(tax_amount) as tax_amount');

        /** @var \stdClass $summary */
        $summary = DB::table(Refund::getTableName())->selectRaw($select)
            ->where('order_id', '=', $orderId)
            ->whereNotIn('status', [Refund::STATUS_FAILED])
            ->groupBy('order_id')
            ->first();

        if($summary){
            return [
                'parts_amount' => Money::of((float)$summary->parts_amount, 'USD'),
                'handling_amount' => Money::of((float)$summary->handling_amount, 'USD'),
                'shipping_amount' => Money::of((float)$summary->shipping_amount, 'USD'),
                'adjustment_amount' => Money::of((float)$summary->adjustment_amount, 'USD'),
                'tax_amount' => Money::of((float)$summary->tax_amount, 'USD'),
            ];
        }
        return [
            'parts_amount' => Money::zero('USD'),
            'handling_amount' => Money::zero('USD'),
            'shipping_amount' => Money::zero('USD'),
            'adjustment_amount' => Money::zero('USD'),
            'tax_amount' => Money::zero('USD'),
        ];
    }

    /**
     * @param Refund $refund
     * @param string|array $message
     * @param string $stage
     * @return bool
     */
    public function markAsFailed(Refund $refund, $message, string $stage): bool
    {
        $refund->status = Refund::STATUS_FAILED;

        return $refund->addError($message, $stage);
    }

    /**
     * @param Refund $refund
     * @param array $metadata
     * @param $message
     * @param string $stage
     * @return bool
     */
    public function markAsRecoverableFailure(Refund $refund, array $metadata, $message, string $stage): bool
    {
        $refund->recoverable_failure_stage = $stage;
        $refund->metadata = $metadata;

        return $refund->addError($message, $stage);
    }

    /**
     * @param Refund $refund
     * @param PaymentGatewayRefundResultInterface $refundResult
     * @return Refund
     */
    public function markAsProcessed(Refund $refund, PaymentGatewayRefundResultInterface $refundResult): Refund
    {
        $this->update(
            $refund->id,
            [
                'status' => Refund::STATUS_PROCESSED,
                'payment_gateway_id' => $refundResult->getId(),
                'metadata' => $refundResult->asArray()
            ]
        );

        return $this->get($refund->id);
    }

    /**
     * @param Refund $refund
     * @param int $textrailRma
     * @return bool
     */
    public function updateRma(Refund $refund, int $textrailRma): bool
    {
        return $this->update($refund->id, ['textrail_rma' => $textrailRma]);
    }

    /**
     * @param Refund $refund
     * @return bool
     */
    public function markAsProcessing(Refund $refund): bool
    {
        return $this->update($refund->id, ['status' => Refund::STATUS_PROCESSING]);
    }

    /**
     * Depending on the total amount, the status will be updated to approved or denied:
     *      - when the total amount is greater than zero it will be marked as approved
     *      - when the total amount is less or equal zero it will be marked as denied
     *
     * @param Refund $refund
     * @param array<array{sku:string, title:string, id:int, amount: float, qty: int, price: float}> $parts
     * @return Refund
     */
    public function markAsApprovedOrDenied(Refund $refund, array $parts): Refund
    {
        // shipping/handling/adjustment are not taken in account due this refund is a return
        $total = $refund->parts_amount + $refund->tax_amount;

        $this->update(
            $refund->id,
            [
                'status' => ($total > 0 ? Refund::STATUS_APPROVED : Refund::STATUS_DENIED),
                'parts_amount' => $refund->parts_amount,
                'tax_amount' => $refund->tax_amount,
                'total_amount' => $total,
                'parts' => $parts,
                'metadata' => array_merge((array)$refund->metadata, ['processed_parts' => $parts])
            ]
        );

        return $this->get($refund->id);
    }

    /**
     * @param Refund $refund
     * @param int $refundTextrailId
     * @return bool
     */
    public function markAsCompleted(Refund $refund, int $refundTextrailId): bool
    {
        return $this->update(
            $refund->id,
            [
                'status' => Refund::STATUS_COMPLETED,
                'textrail_refund_id' => $refundTextrailId
            ]
        );
    }

    /**
     * @param int $refundId
     * @param array|string $error
     * @param string $stage
     * @return bool
     */
    public function logError(int $refundId, $error, string $stage): bool
    {
        return $this->get($refundId)->addError($error, $stage);
    }

    /**
     * @param int $refundId
     * @param array $params
     * @return bool
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    private function update(int $refundId, array $params = []): bool
    {
        return Refund::findOrFail($refundId)->fill($params)->save();
    }
}
