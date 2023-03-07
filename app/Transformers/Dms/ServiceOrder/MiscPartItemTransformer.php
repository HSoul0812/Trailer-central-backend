<?php


namespace App\Transformers\Dms\ServiceOrder;


use App\Models\CRM\Dms\ServiceOrder\MiscPartItem;
use League\Fractal\TransformerAbstract;

class MiscPartItemTransformer extends TransformerAbstract
{
    public function transform(MiscPartItem $item)
    {
        return [
            'id' => (int)$item->id,
            'repair_order_id' => (int)$item->repair_order_id,
            'title' => $item->title,
            'dealer_cost' => (float)$item->dealer_cost,
            'unit_price' => (float)$item->unit_price,
            'quantity' => (int)$item->quantity,
            'notes' => $item->notes,
            'mpi_taxable' => $item->taxable,
        ];
    }
}
