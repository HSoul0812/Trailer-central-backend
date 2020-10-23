<?php


namespace App\Transformers\Dms\ServiceOrder;


use App\Models\CRM\Dms\ServiceOrder\LaborCode;
use App\Models\CRM\Dms\ServiceOrder\OtherItem;
use League\Fractal\TransformerAbstract;

class OtherItemTransformer extends TransformerAbstract
{
    public function transform(OtherItem $item)
    {
        return [
            'id' => (int)$item->id,
            'vendor_id' => (int)$item->vendor_id,
            'type' => $item->type,
            'description' => $item->description,
            'cost' => (float)$item->cost,
            'amount' => (float)$item->amount,
            'notes' => $item->notes,
        ];
    }
}
