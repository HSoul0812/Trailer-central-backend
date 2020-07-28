<?php

namespace App\Transformers\Dms\PurchaseOrder;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrderPart;
use App\Transformers\Quickbooks\ItemTransformer;

class PurchaseOrderPartTransformer extends TransformerAbstract
{

    public function transform(PurchaseOrderPart $poPart)
    {   
        return [
            'id' => $poPart->id,
            'act_cost' => $poPart->act_cost,
            'qty' => $poPart->qty,
            'qb_item' => (new ItemTransformer())->transform($poPart->qbItem)
        ];
    }

} 