<?php

namespace App\Transformers\Dms;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Dms\ServiceOrder;

class ServiceOrderTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['invoice'];

    public function transform($serviceOrder)
    {
        return [
            'id' => $serviceOrder->id,
            'dealer_id' => $serviceOrder->dealer_id,
            'user_defined_id' => $serviceOrder->user_defined_id,
            'customer' => $serviceOrder->customer,
            'created_at' => $serviceOrder->created_at,
            'closed_at' => $serviceOrder->closed_at,
            'total_price' => $serviceOrder->total_price,
            'invoice' => $serviceOrder->invoice,
            'location' => $serviceOrder->dealerLocation ? $serviceOrder->dealerLocation->name : null,
            'paid_amount' => (float) $serviceOrder->paid_amount,
            'status' => $serviceOrder->status,
            'status_name' => ServiceOrder::SERVICE_ORDER_STATUS[$serviceOrder->status],
        ];
    }

    public function withInvoice(ServiceOrder $serviceOrder)
    {
        return $this->item($serviceOrder->invoice, new InvoiceTransformer());
    }
}
