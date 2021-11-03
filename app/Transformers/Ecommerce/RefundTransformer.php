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
                'amount' => $refund->amount,
                'reason' => $refund->reason,
                'status' => $refund->status,
                'parts' => $refund->parts,
                'created_at' => $refund->created_at->format('Y-m-d H:i:s'),
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
