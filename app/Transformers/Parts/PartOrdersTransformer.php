<?php

namespace App\Transformers\Parts;

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
             'shipto' => $order->ship_to,
             'cart_items' => $order->cart_items,
             'subtotal' => number_format($order->subtotal, 2),
             'tax' => number_format($order->tax, 2),
             'shipping' => number_format($order->shipping, 2),
             'total' => $order->total,
             'order_key' => $order->order_key,
             'created_at' => date('Y-m-d H:i', strtotime($order->created_at)),
             'updated_at' => date('Y-m-d H:i', strtotime($order->updated_at))
         ];
    }
}
