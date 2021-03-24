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
             'shipto' => $order->ship_to,
             'shipto_name' => $order->shipto_name,
             'shipto_address' => $order->shipto_address,
             'shipto_city' => $order->shipto_city,
             'shipto_region' => $order->shipto_region,
             'shipto_postal' => $order->shipto_postal,
             'shipto_country' => $order->shipto_country,
             'billto' => $order->bill_to,
             'billto_name' => $order->billto_name,
             'billto_address' => $order->billto_address,
             'billto_city' => $order->billto_city,
             'billto_region' => $order->billto_region,
             'billto_postal' => $order->billto_postal,
             'billto_country' => $order->billto_country,
             'cart_items' => $order->cart_items,
             'subtotal' => number_format($order->subtotal, 2),
             'tax' => number_format($order->tax, 2),
             'shipping' => number_format($order->shipping, 2),
             'total' => number_format($order->total, 2),
             'order_key' => $order->order_key,
             'created_at' => date('Y-m-d H:i', strtotime($order->created_at)),
             'updated_at' => date('Y-m-d H:i', strtotime($order->updated_at))
         ];
    }
}
