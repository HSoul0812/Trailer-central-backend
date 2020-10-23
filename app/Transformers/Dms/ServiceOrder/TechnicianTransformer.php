<?php


namespace App\Transformers\Dms\ServiceOrder;


use App\Models\CRM\Dms\ServiceOrder\Technician;
use League\Fractal\TransformerAbstract;

class TechnicianTransformer extends TransformerAbstract
{
    public function transform(Technician $item)
    {
        return [
            'id' => (int)$item->id,
            'first_name' => $item->first_name,
            'last_name' => $item->last_name,
            'full_name' => $item->first_name . ' ' . $item->last_name,
            'email' => $item->email ,
            'hourly_rate' => $item->hourly_rate,
        ];
    }
}
