<?php

namespace App\Transformers\Dms;

use App\Transformers\Dms\ServiceOrder\MiscPartItemTransformer;
use App\Transformers\Dms\ServiceOrder\OtherItemTransformer;
use App\Transformers\Dms\ServiceOrder\PartItemTransformer;
use App\Transformers\Dms\ServiceOrder\ServiceItemTechnicianTransformer;
use App\Transformers\Dms\ServiceOrder\ServiceItemTransformer;
use League\Fractal\TransformerAbstract;
use App\Models\CRM\Dms\ServiceOrder;

class ServiceOrderTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'serviceItems', 'partItems', 'miscPartItems', 'otherItems', 'invoice'
    ];

    public function transform($serviceOrder)
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
            'total_price' => $serviceOrder->total_price,
            'invoice' => $serviceOrder->invoice,
            'receipts' => $this->getReceipts($serviceOrder),
            'location' => $serviceOrder->dealerLocation ? $serviceOrder->dealerLocation->name : null,
            'paid_amount' => (float) $serviceOrder->paid_amount,
            'status' => $serviceOrder->status,
            'status_name' => ServiceOrder::SERVICE_ORDER_STATUS[$serviceOrder->status],
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

}
