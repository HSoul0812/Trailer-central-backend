<?php


namespace App\Transformers\Dms;


use App\Models\CRM\Dms\RefundItem;
use App\Transformers\Quickbooks\ItemTransformer;
use League\Fractal\TransformerAbstract;

class RefundItemTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'item'
    ];

    public function transform(RefundItem $refundItem)
    {
        return [
            'id' => $refundItem->id,
            // 'dealer_refunds_id' => $refundItem->dealer_refunds_id,
            // 'user_id' => (int)$refundItem->user_id, // express as include
            'item_id' => (int)$refundItem->item_id,
            'amount' => (float)$refundItem->amount,
            'quantity' => (float)$refundItem->quantity,
            'created_at' => $refundItem->created_at,
            'updated_at' => $refundItem->updated_at,
        ];
    }

    public function includeItem(RefundItem $refundItem)
    {
        return $this->item($refundItem->item, new ItemTransformer());
    }
}
