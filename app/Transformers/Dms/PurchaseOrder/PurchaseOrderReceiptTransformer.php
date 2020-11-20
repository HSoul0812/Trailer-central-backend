<?php

namespace App\Transformers\Dms\PurchaseOrder;

use League\Fractal\TransformerAbstract;
use Illuminate\Database\Eloquent\Collection;

use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrderReceipt;
use App\Transformers\Dms\PurchaseOrder\PurchaseOrderPartReceivedTransformer;
use App\Transformers\Dms\PurchaseOrder\PurchaseOrderInventoryTransformer;

class PurchaseOrderReceiptTransformer extends TransformerAbstract
{

    protected $partReceivedTransformer;
    protected $poInventoryTransformer;

    public function __construct()
    {
        $this->partReceivedTransformer = new PurchaseOrderPartReceivedTransformer();
        $this->poInventoryTransformer = new PurchaseOrderInventoryTransformer();
    }

    public function transform(PurchaseOrderReceipt $poReceipt)
    {
        return [
            'id' => $poReceipt->id,
            // 'purchase_order' => $poReceipt->purchaseOrder,
            'ref_num' => $poReceipt->ref_num,
            'memo' => $poReceipt->memo,
            'created_at' => $poReceipt->created_at,
            'partsReceived' => $poReceipt->receivedParts ? $this->transformPartReceived($poReceipt->receivedParts) : [],
            'inventoriesReceived' => $poReceipt->receivedInventories ? $this->transformPoInventory($poReceipt->receivedInventories) : []
        ];
    }

    private function transformPartReceived(Collection $partsReceived)
    {
        $ret = [];
        foreach($partsReceived as $part) {
            $ret[] = $this->partReceivedTransformer->transform($part);
        }
        return $ret;
    }

    private function transformPoInventory(Collection $inventoriesReceived)
    {
        $ret = [];
        foreach($inventoriesReceived as $poInventory) {
            $ret[] = $this->poInventoryTransformer->transform($poInventory);
        }
        return $ret;
    }

} 