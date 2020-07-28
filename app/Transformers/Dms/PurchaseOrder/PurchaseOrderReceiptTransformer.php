<?php

namespace App\Transformers\Dms\PurchaseOrder;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Dms\PurchaseOrderReceipt;

class PurchaseOrderReceiptTransformer extends TransformerAbstract
{

    public function transform(PurchaseOrderReceipt $poReceipt)
    {   
        return [
            'id' => $poReceipt->id,
            'purchase_order' => $poReceipt->purchaseOrder,
            'ref_num' => $poReceipt->ref_num,
            'memo' => $poReceipt->memo,
            'created_at' => $poReceipt->created_at,
        ];
    }
} 