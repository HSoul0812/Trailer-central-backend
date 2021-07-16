<?php

namespace App\Transformers\Dms;

use App\Transformers\Dms\ServiceOrder\MiscPartItemTransformer;
use App\Transformers\Dms\ServiceOrder\OtherItemTransformer;
use App\Transformers\Dms\ServiceOrder\PartItemTransformer;
use App\Transformers\Dms\ServiceOrder\ServiceItemTransformer;
use App\Transformers\Inventory\InventoryTransformerV2;
use League\Fractal\TransformerAbstract;
use App\Models\CRM\Dms\ServiceOrder;

class ServiceOrderTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'serviceItems', 'partItems', 'miscPartItems', 'otherItems', 'invoice', 'inventory'
    ];

    public function transform(ServiceOrder $serviceOrder): array
    {
        return [
            'id' => $serviceOrder->id,
            'dealer_id' => $serviceOrder->dealer_id,
            'user_defined_id' => $serviceOrder->user_defined_id,
            'customer' => $serviceOrder->customer,
            'created_at' => $serviceOrder->created_at,
            'notified_at' => $serviceOrder->notified_at,
            'date_in' => $serviceOrder->date_in,
            'date_out' => $serviceOrder->date_out,
            'closed_at' => $serviceOrder->closed_at,
            'closed_by_related_unit_sale' => (boolean) $serviceOrder->closed_by_related_unit_sale,
            'type'      => $serviceOrder->type,
            'total_price' => $serviceOrder->total_price,
            'labor_discount' => $serviceOrder->labor_discount,
            'part_discount' => $serviceOrder->part_discount,
            'invoice' => $serviceOrder->invoice,
            'receipts' => $this->getReceipts($serviceOrder),
            'location' => $serviceOrder->dealerLocation ? $serviceOrder->dealerLocation->name : null,
            'paid_amount' => (float) $serviceOrder->paid_amount,
            'status' => $serviceOrder->status,
            'status_name' => ServiceOrder::SERVICE_ORDER_STATUS[$serviceOrder->status],
            'shipping' => (float) ($serviceOrder->shipping > 0 ? $serviceOrder->shipping : 0.0),
            'inventory'     => $serviceOrder->inventory,
            'public_memo'   => $serviceOrder->public_memo,
            'private_memo'  => $serviceOrder->private_memo,
            'services'      => $serviceOrder->serviceItems,
        ];
    }

    public function includeServiceItems(ServiceOrder $serviceOrder)
    {
        return $this->collection($serviceOrder->serviceItems, new ServiceItemTransformer());
    }

    public function includePartItems(ServiceOrder $serviceOrder)
    {
        return $this->collection($serviceOrder->partItems, new PartItemTransformer());
    }

    public function includeMiscPartItems(ServiceOrder $serviceOrder)
    {
        return $this->collection($serviceOrder->miscPartItems, new MiscPartItemTransformer());
    }

    public function includeOtherItems(ServiceOrder $serviceOrder)
    {
        return $this->collection($serviceOrder->otherItems, new OtherItemTransformer());
    }

    public function withInvoice(ServiceOrder $serviceOrder)
    {
        return $this->item($serviceOrder->invoice, new InvoiceTransformer());
    }

    private function getReceipts(ServiceOrder $serviceOrder)
    {
        $receipts = [];

        if ($serviceOrder->invoice && $serviceOrder->invoice->payments) {
            foreach($serviceOrder->invoice->payments as $payment) {
                $receipts[] = $payment->receipts;
            }
        }

        return $receipts;
    }

    public function includeInventory(ServiceOrder $serviceOrder)
    {
        if (!empty($serviceOrder->inventory)) {
            return $this->item($serviceOrder->inventory, new InventoryTransformerV2());
        }
        return null;
    }

}
