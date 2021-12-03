<?php

declare(strict_types=1);

namespace App\Http\Requests\Ecommerce\Refund;

use App\Http\Requests\Request;
use App\Models\Ecommerce\Refund;
use App\Models\Ecommerce\RefundTextrailStatuses;
use App\Repositories\Ecommerce\RefundRepositoryInterface;
use Illuminate\Validation\Rule;

/**
 * @property int $Rma Order return id
 * @property array<array{sku: string, qty: int}> $Items
 * @property string $Status Textrail status as they used
 */
class UpdateRefundTextrailRequest extends Request
{
    public function getRules(): array
    {
        return [
            'Rma' => 'integer|min:1|required|exists:ecommerce_order_refunds,textrail_rma',
            'Status' => ['required', 'string', Rule::in(array_keys(RefundTextrailStatuses::MAP))],
            'Items' => 'array|required',
            'Items.*.Sku' => 'required',
            'Items.*.Qty' => 'required|integer|min:1'
        ];
    }

    public function refund(): ?Refund
    {
        return $this->repository()->getByRma((int)$this->Rma);
    }

    public function mappedStatus(): ?string
    {
        return $this->status ?? RefundTextrailStatuses::MAP[$this->Status] ?? Refund::STATUS_PENDING;
    }

    /**
     * @return array array of parts indexed by part sku
     */
    public function parts(): array
    {
        $indexedParts = [];

        foreach ($this->input('Items', []) as $part) {
            $indexedParts[$part['Sku']] = ['sku' => $part['Sku'], 'qty' => $part['Qty']];
        }

        return $indexedParts;
    }

    protected function repository(): RefundRepositoryInterface
    {
        return app(RefundRepositoryInterface::class);
    }
}
