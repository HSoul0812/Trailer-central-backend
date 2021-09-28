<?php
namespace App\Transformers\Ecommerce;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use League\Fractal\TransformerAbstract;

class CompletedOrderTransformer extends TransformerAbstract
{
    public function transform(CompletedOrder $completedOrder)
    {
        return [
            'id' => $completedOrder->id,
            'hook_event_id' => $completedOrder->event_id,
            'object_id' => $completedOrder->object_id,
            'customer_email' => $completedOrder->customer_email,
            'total_amount' => $completedOrder->total_amount,
            'payment_method' => $completedOrder->payment_method,
            'payment_status' => $completedOrder->payment_status,
            'stripe_customer_id' => $completedOrder->stripe_customer,
            'shipping_address' => $completedOrder->shipping_address,
            'billing_address' => $completedOrder->billing_address,
            'postal_code' => $completedOrder->postal_code,
            'created_at' => $completedOrder->created_at
        ];
    }
}