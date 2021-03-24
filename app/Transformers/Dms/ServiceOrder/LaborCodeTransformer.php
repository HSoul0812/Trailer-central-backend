<?php


namespace App\Transformers\Dms\ServiceOrder;


use App\Models\CRM\Dms\ServiceOrder\LaborCode;
use League\Fractal\TransformerAbstract;

class LaborCodeTransformer extends TransformerAbstract
{
    public function transform(?LaborCode $item)
    {
        if (empty($item)) {
            return [];
        }
        
        return [
            'id' => (int)$item->id,
            'dealer_id' => (int)$item->dealer_id,
            'name' => $item->name,
            'hourly_rate' => (float)$item->hourly_rate,
            'price' => (float)$item->price,
        ];
    }
}
