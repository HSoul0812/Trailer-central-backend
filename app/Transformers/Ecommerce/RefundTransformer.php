<?php

declare(strict_types=1);

namespace App\Transformers\Ecommerce;

use League\Fractal\Resource\Primitive;
use App\Models\Ecommerce\Refund;
use League\Fractal\TransformerAbstract;

class RefundTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'order'
    ];

    public function transform(Refund $refund): array
    {
        $extraData = $this->requestHasIncludeOrder() ? [] : ['order_id' => $refund->order_id];

        return [
                'id' => $refund->id,
                'total_amount' => $refund->total_amount,
                'parts_amount' => $refund->parts_amount,
                'tax_amount' => $refund->tax_amount,
                'shipping_amount' => $refund->shipping_amount,
                'handling_amount' => $refund->handling_amount,
                'adjustment_amount' => $refund->adjustment_amount,
                'reason' => $refund->reason,
                'status' => $refund->status,
                'parts' => $refund->parts,
                'payment_gateway_id' => $refund->payment_gateway_id,
                'textrail_rma' => $refund->textrail_rma,
                'textrail_refund_id' => $refund->textrail_refund_id,
                'created_at' => $refund->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $refund->updated_at ? $refund->updated_at->format('Y-m-d H:i:s') : null,
            ] + $extraData;
    }

    public function includeOrder(Refund $refund): Primitive
    {
        return $this->primitive($refund->order, app(CompletedOrderTransformer::class));
    }

    private function requestHasIncludeOrder(): bool
    {
        return in_array('order', $this->currentScope->getManager()->getRequestedIncludes());
    }
}
