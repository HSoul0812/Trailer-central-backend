<?php

namespace App\Transformers\Dms\PurchaseOrder;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrderInventory;
use App\Transformers\Quickbooks\ItemTransformer;

class PurchaseOrderInventoryTransformer extends TransformerAbstract
{

    public function transform(PurchaseOrderInventory $poInventory)
    {   
        return [
            'id' => $poInventory->id,
            'act_cost' => $poInventory->act_cost,
            'qty' => $poInventory->qty,
            'qb_item' => (new ItemTransformer())->transform($poInventory->qbItem)
        ];
    }

} 