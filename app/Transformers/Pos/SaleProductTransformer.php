<?php


namespace App\Transformers\Pos;


use App\Models\Pos\SaleProduct;
use App\Transformers\Quickbooks\ItemTransformer;
use League\Fractal\TransformerAbstract;

class SaleProductTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'item'
    ];

    public function transform(SaleProduct $saleProduct)
    {
        return [
            'qty' => (int)$saleProduct->qty,
            'price' => (float)$saleProduct->price,
            'item_id' => (int)$saleProduct->item_id
        ];
    }

    public function includeItem(SaleProduct $saleProduct)
    {
        return $this->item($saleProduct->item, new ItemTransformer());
    }
}
