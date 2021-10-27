<?php
namespace App\Transformers\Dms\Bill;

use App\Models\CRM\Dms\Quickbooks\Bill;
use League\Fractal\TransformerAbstract;

class BillTransformer extends TransformerAbstract
{
    public function transform(Bill $bill)
    {
        return [
            'id' => $bill->id,
            'status' => $bill->status,
            'vendor_id' => $bill->vendor_id,
            'dealer_id' => $bill->dealer_id,
            'dealer_location_id' => $bill->dealer_location_id,
            'doc_num' => $bill->doc_num,
            'received_date' => $bill->received_date,
            'total' => $bill->total,
            'due_date' => $bill->due_date,
            'packing_list_no' => $bill->packing_list_no,
            'qb_id' => $bill->qb_id,
            'items' => $bill->items,
            'categories' => $bill->categories,
            'payments' => $bill->payments,
        ];
    }
}