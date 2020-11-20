<?php

namespace App\Transformers\Dms\PurchaseOrder;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrderInventory;
use App\Transformers\Quickbooks\ItemTransformer;

class PurchaseOrderInventoryTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['qb_item'];

    public function transform(PurchaseOrderInventory $poInventory)
    {   
        return [
            'id' => $poInventory->id,
            'act_cost' => $poInventory->act_cost,
            'qty' => $poInventory->qty
        ];
    }

    public function includeQbItem(PurchaseOrderInventory $poInventory)
    {
        return $this->item($poInventory->qbItem, new ItemTransformer());
    }

} 