<?php


namespace App\Transformers\Pos;


use App\Models\Pos\Sale;
use App\Transformers\Dms\RefundTransformer;
use League\Fractal\TransformerAbstract;

class SaleTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'products',
        'refunds',
    ];

    public function transform(Sale $sale)
    {
        return [
            'id' => (int)$sale->id,
            //'register_id' => (int)$sale->register_id, // express as include, uncomment if needed
            //'customer_id' => (int)$sale->customer_id, // express as include
            //'sales_person_id' => (int)$sale->sales_person_id,
            //'payment_method_id' => (int)$sale->payment_method_id,
            'subTotal' => (float)$sale->subTotal,
            'discount' => (float)$sale->discount,
            'total' => (float)$sale->total,
            'amount_received' => (float)$sale->amount_received,
            'check_no' => $sale->check_no,
            'check_name' => $sale->check_name,
            // 'qb_id' => (int)$sale->qb_id,
            // 'related_payment_intent' => $sale->related_payment_intent,
            'shipping' => (float)$sale->shipping,
            'created_at' => $sale->created_at,
        ];
    }

    public function includeProducts(Sale $sale)
    {
        return $this->collection($sale->products, new SaleProductTransformer());
    }

    public function includeRefunds(Sale $sale)
    {
        return $this->collection($sale->refunds, new RefundTransformer());
    }

}
