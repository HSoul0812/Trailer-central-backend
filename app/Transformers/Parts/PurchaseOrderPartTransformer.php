<?php

declare(strict_types=1);

namespace App\Transformers\Parts;

use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrderPart;
use League\Fractal\TransformerAbstract;

class PurchaseOrderPartTransformer extends TransformerAbstract
{
    public function transform(PurchaseOrderPart $poPart): array
    {
        return $poPart->purchaseOrder->only([
            'status',
            'user_defined_id',
            'id',
            'receive_purchase_order_crm_url',
        ]);
    }
}
