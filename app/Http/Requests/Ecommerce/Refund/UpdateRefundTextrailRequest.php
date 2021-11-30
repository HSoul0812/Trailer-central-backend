<?php

declare(strict_types=1);

namespace App\Http\Requests\Ecommerce\Refund;

use App\Http\Requests\Request;
use App\Models\Ecommerce\Refund;
use App\Models\Ecommerce\RefundTextrailStatuses;
use App\Repositories\Ecommerce\RefundRepositoryInterface;
use Illuminate\Validation\Rule;

/**
 * @property int $order_id Textrail order id
 * @property int $rma Order return id
 * @property array<{sku: string, qty: int}> $items
 * @property string $status Textrail status as they used
 */
class UpdateRefundTextrailRequest extends Request
{
    public function getRules(): array
    {
        return [
            'order_id' => 'integer|min:1|required|exists:ecommerce_completed_orders,ecommerce_order_id',
            'rma' => 'integer|min:1|required|exists:ecommerce_order_refunds,textrail_rma',
            'status' => ['required', 'string', Rule::in(array_keys(RefundTextrailStatuses::MAP))],
            'items' => 'array|required',
            'items.*.sku' => 'required|string|min:1',
            'items.*.qty' => 'required|int:min:1'
        ];
    }

    public function getRefund(): ?Refund
    {
        return $this->getRepository()->getByRma((int)$this->rma);
    }

    public function getMappedStatus(): ?string
    {
        return $this->status ?? RefundTextrailStatuses::MAP[$this->status] ?? RefundTextrailStatuses::PENDING;
    }

    protected function getRepository(): RefundRepositoryInterface
    {
        return app(RefundRepositoryInterface::class);
    }
}
