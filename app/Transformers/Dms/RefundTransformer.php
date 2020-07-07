<?php


namespace App\Transformers\Dms;


use App\Models\CRM\Dms\Refund;
use League\Fractal\TransformerAbstract;

class RefundTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'items'
    ];

    public function transform(Refund $refund)
    {
        return [
            'id' => (int)$refund->id,
            'tb_name' => $refund->tb_name,
            'tb_primary_id' => (int)$refund->tb_primary_id,
            'amount' => (float)$refund->amount,
            'created_at' => $refund->created_at,
            'updated_at' => $refund->updated_at,
        ];
    }

    public function includeItems(Refund $refund)
    {
        return $this->collection($refund->items, new RefundItemTransformer());
    }
}
