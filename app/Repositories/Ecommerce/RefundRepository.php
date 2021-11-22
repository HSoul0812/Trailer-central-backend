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
        return Refund::create($params);
    }

    public function get(int $refundId): ?Refund
    {
        return Refund::find($refundId);
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

        $amountAdder = static function (int $id, float $amount) use (&$partsAmount) {
            return isset($partsAmount[$id]) ? $partsAmount[$id] + $amount : $amount;
        };

        $qtyAdder = static function (int $id, int $qty) use (&$partsQty) {
            return isset($partsQty[$id]) ? $partsQty[$id] + $qty : $qty;
        };

        // we'll make an two arrays of parts with their total refunded amount a qty indexed by part id
        $this->getAll(['order_id' => $orderId])
            ->each(static function (Refund $refund) use (&$partsAmount, &$partsQty, $amountAdder, $qtyAdder) {
                foreach ($refund->parts as $part) {
                    $partsAmount[$part['id']] = $amountAdder($part['id'], $part['amount']);
                    $partsQty[$part['id']] = $qtyAdder($part['id'], $part['qty']);
                }
            });

        return Part::query()
            ->whereIn('id', array_keys($partsAmount))
            ->get()
            ->map(static function (Part $part) use ($partsAmount, $partsQty): RefundedPart {
                return RefundedPart::from([
                    'id' => $part->id,
                    'title' => $part->title,
                    'sku' => $part->sku,
                    'amount' => $partsAmount[$part->id],
                    'qty' => $partsQty[$part->id]
                ]);
            });
    }

    /**
     * @param array<int> $parts
     * @return array<Part>|Collection
     */
    public function getPartsToBeRefunded(array $parts): Collection
    {
        return Part::query()->whereIn('id', array_unique($parts))->get();
    }

    public function getRefundedAmount(int $orderId): Money
    {
        $amount = (float)Refund::query()
            ->where('order_id', '=', $orderId)
            ->where('status', '!=', Refund::STATUS_FAILED)
            ->sum('total_amount');

        return Money::of($amount, 'USD', null, RoundingMode::DOWN);
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
            ->where('status', '!=', Refund::STATUS_FAILED)
            ->groupBy('order_id')
            ->first();

        return [
            'parts_amount' => Money::of((float)$summary->parts_amount, 'USD'),
            'handling_amount' => Money::of((float)$summary->handling_amount, 'USD'),
            'shipping_amount' => Money::of((float)$summary->shipping_amount, 'USD'),
            'adjustment_amount' => Money::of((float)$summary->adjustment_amount, 'USD'),
            'tax_amount' => Money::of((float)$summary->tax_amount, 'USD'),
        ];
    }

    /**
     * @param Refund $refund
     * @param string $errorMessage
     * @return bool
     */
    public function markAsFailed(Refund $refund, string $errorMessage): bool
    {
        return $this->update(
            $refund->id,
            [
                'status' => Refund::STATUS_FAILED,
                'metadata' => ['error' => $errorMessage]
            ]
        );
    }

    /**
     * @param Refund $refund
     * @param string $stage
     * @param array $data
     * @return bool
     */
    public function markAsRecoverableFailure(Refund $refund, string $stage, array $data): bool
    {
        return $this->update($refund->id, $data + ['recoverable_failure_stage' => $stage, 'status' => Refund::STATUS_FAILED]);
    }

    /**
     * @param Refund $refund
     * @param PaymentGatewayRefundResultInterface $refundResult
     * @return bool
     */
    public function markAsCompleted(Refund $refund, PaymentGatewayRefundResultInterface $refundResult): bool
    {
        return $this->update(
            $refund->id,
            ['payment_gateway_id' => $refundResult->getId(), 'metadata' => $refundResult->asArray()]
        );
    }

    /**
     * @param Refund $refund
     * @param int $textrailId
     * @return bool
     */
    public function markAsAuthorized(Refund $refund, int $textrailId): bool
    {
        return $this->update(
            $refund->id,
            [
                'status' => Refund::STATUS_AUTHORIZED,
                'textrail_id' => $textrailId
            ]
        );
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
