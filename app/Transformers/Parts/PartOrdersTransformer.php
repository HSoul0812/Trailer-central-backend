<?php

namespace App\Transformers\PartOrders;

use League\Fractal\TransformerAbstract;
use App\Models\Parts\PartOrder;

class PartOrdersTransformer extends TransformerAbstract
{
    public function transform(PartOrder $order)
    {
	 return [
             'id' => (int)$order->id,
             'dealer_id' => (int)$order->dealer_id,
             'website_id' => (int)$order->website_id,
             'status' => $order->status,
             'fulfillment' => $order->fulfillment_type,
             'email' => $order->email_address,
             'phone' => $order->phone_number,
             'shipto_name' => $order->shipto_name,
             'shipto_address' => $order->shipto_address,
             'cart_items' => $order->cart_items,
             'subtotal' => number_format($order->subtotal, 2),
             'tax' => number_format($order->tax, 2),
             'shipping' => number_format($order->shipping, 2),
             'order_key' => $order->order_key
         ];
    }
}
