<?php


namespace App\Transformers\Dms\ServiceOrder;


use App\Models\CRM\Dms\ServiceOrder\ServiceItem;
use App\Models\CRM\Dms\ServiceOrder\ServiceItemTechnician;
use League\Fractal\TransformerAbstract;

class ServiceItemTechniciansTransformer extends TransformerAbstract
{
    public function transform(ServiceItemTechnician $item)
    {
        return [
            'id' => (int)$item->id,
            'service_item_id' => (int)$item->service_item_id,
            'dms_settings_technician_id' => (int)$item->dms_settings_technician_id,
            'act_hrs' => (float)$item->act_hrs,
            'paid_hrs' => (float)$item->paid_hrs,
            'billed_hrs' => (float)$item->billed_hrs,
            'discount' => (float)$item->discount,
            'is_completed' => (int)$item->is_completed,
            'start_date' => $item->start_date,
            'completed_date' => $item->completed_date,
            'miles_in' => (float)$item->miles_in,
            'miles_out' => (float)$item->miles_out,
        ];
    }
}
