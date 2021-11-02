<?php

declare(strict_types=1);

namespace App\Repositories\Ecommerce;

use App\Models\Ecommerce\Refund;
use App\Models\Parts\Textrail\Part;
use App\Models\Parts\Textrail\RefundedPart;
use App\Services\Ecommerce\Payment\RefundResultInterface;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Illuminate\Support\Collection;

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
     * @param array $params
     * @return array<Refund>|Collection
     * @throws \InvalidArgumentException when "order_id" argument was not provided
     */
    public function getAll(array $params): Collection
    {
        $query = Refund::query();

        if (empty($params['order_id'])) {
            throw new \InvalidArgumentException("'order_id' filter is required");
        }

        return $query
            ->where('order_id', '=', $params['order_id'])
            ->where('status', '!=', Refund::STATUS_FAILED)
            ->get();
    }

    /**
     * @param int $orderId
     * @return array<RefundedPart>|Collection
     */
    public function getRefundedParts(int $orderId): Collection
    {
        $parts = [];

        $adder = static function (int $id, float $amount) use (&$parts) {
            return isset($parts[$id]) ? $parts[$id] + $amount : $amount;
        };

        // we'll make an array of parts with their total refunded amount indexed by part id
        $this->getAll(['order_id' => $orderId])
            ->each(static function (Refund $refund) use (&$parts, $adder) {
                foreach ($refund->parts as $part) {
                    $parts[$part['id']] = $adder($part['id'], $part['amount']);
                }
            });

        return Part::query()
            ->whereIn('id', array_keys($parts))
            ->get()
            ->map(static function (Part $part) use ($parts): RefundedPart {
                return RefundedPart::from([
                    'id' => $part->id,
                    'title' => $part->title,
                    'sku' => $part->sku,
                    'amount' => $parts[$part->id]
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
            ->sum('amount');

        return Money::of($amount, 'USD', null, RoundingMode::DOWN);
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
     * When there was some error after the refund has been done successfully on the payment gateway side,
     * it should be provided a result in order to make traceable
     *
     * @param Refund $refund
     * @param RefundResultInterface $refundResult
     * @return bool
     */
    public function markAsRecoverableFailure(Refund $refund, RefundResultInterface $refundResult): bool
    {
        return $this->update(
            $refund->id,
            [
                'status' => Refund::STATUS_RECOVERABLE_FAILURE,
                'object_id' => $refundResult->getId(),
                'metadata' => $refundResult->asArray()
            ]
        );
    }

    /**
     * @param Refund $refund
     * @param RefundResultInterface $refundResult
     * @return bool
     */
    public function markAsFinished(Refund $refund, RefundResultInterface $refundResult): bool
    {
        return $this->update(
            $refund->id,
            ['object_id' => $refundResult->getId(), 'metadata' => $refundResult->asArray()]
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
