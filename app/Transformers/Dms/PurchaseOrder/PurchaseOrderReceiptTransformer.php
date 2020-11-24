<?php

namespace App\Transformers\Dms\PurchaseOrder;

use League\Fractal\TransformerAbstract;

use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrderReceipt;
use App\Transformers\Dms\PurchaseOrder\PurchaseOrderPartReceivedTransformer;
use App\Transformers\Dms\PurchaseOrder\PurchaseOrderInventoryTransformer;

class PurchaseOrderReceiptTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['partsReceived', 'inventoriesReceived'];

    public function transform(PurchaseOrderReceipt $poReceipt)
    {
        return [
            'id' => $poReceipt->id,
            'ref_num' => $poReceipt->ref_num,
            'memo' => $poReceipt->memo,
            'created_at' => $poReceipt->created_at
        ];
    }

    public function includePartsReceived(PurchaseOrderReceipt $poReceipt)
    {
        if (!empty($poReceipt->receivedParts)) {
            return $this->collection($poReceipt->receivedParts, new PurchaseOrderPartReceivedTransformer());
        }
        return [];
    }

    public function includeInventoriesReceived(PurchaseOrderReceipt $poReceipt)
    {
        if (!empty($poReceipt->receivedInventories)) {
            return $this->collection($poReceipt->receivedInventories, new PurchaseOrderInventoryTransformer());
        }
        return [];
    }

} 