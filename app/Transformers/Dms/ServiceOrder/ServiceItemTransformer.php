<?php


namespace App\Transformers\Dms\ServiceOrder;


use App\Models\CRM\Dms\ServiceOrder\LaborCode;
use App\Models\CRM\Dms\ServiceOrder\ServiceItem;
use App\Transformers\Dms\ServiceOrderTransformer;
use League\Fractal\TransformerAbstract;

class ServiceItemTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'laborCode', 'technicians', 'serviceOrder',
    ];

    public function transform(ServiceItem $item)
    {
        return [
            'id' => (int)$item->id,
            'repair_no' => $item->repair_no,
            'claim_no' => $item->claim_no,
            'cause' => $item->cause,
            'labor_code_id' => (int)$item->labor_code_id,
            'job_status' => $item->job_status,
            'problem' => $item->problem,
            'solution' => $item->solution,
            'amount' => (float)$item->amount,
            'notes' => $item->notes,
            'quantity' => (int)$item->quantity,
        ];
    }

    public function includeLaborCode(ServiceItem $item)
    {
        return $this->item($item->laborCode ?: new LaborCode(), new LaborCodeTransformer());
    }

    public function includeTechnicians(ServiceItem $item)
    {
        return $this->collection($item->technicians, new ServiceItemTechnicianTransformer());
    }

    public function includeServiceOrder(ServiceItem $item)
    {
        return $this->item($item->serviceOrder, new ServiceOrderTransformer());
    }

}
