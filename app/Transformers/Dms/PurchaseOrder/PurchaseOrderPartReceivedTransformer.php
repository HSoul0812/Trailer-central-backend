<?php

namespace App\Transformers\Dms\PurchaseOrder;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrderPartReceived;
use App\Transformers\Dms\PurchaseOrder\PurchaseOrderPartTransformer;

class PurchaseOrderPartReceivedTransformer extends TransformerAbstract
{

    public function transform(PurchaseOrderPartReceived $partReceived)
    {   
        return [
            'id' => $partReceived->id,
            'qty' => $partReceived->qty,
            'item' => (new PurchaseOrderPartTransformer())->transform($partReceived->purchaseOrderItem)
        ];
    }

} 