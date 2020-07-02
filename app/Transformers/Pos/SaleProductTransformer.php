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

// CREATE TABLE `crm_pos_sale_products` (
//  `id` int(11) NOT NULL AUTO_INCREMENT,
//  `sale_id` int(11) NOT NULL,
//  `item_id` int(11) NOT NULL,
//  `qty` int(11) NOT NULL,
//  `price` decimal(10,2) DEFAULT NULL,
//  PRIMARY KEY (`id`)
//) ENGINE=InnoDB AUTO_INCREMENT=1065 DEFAULT CHARSET=utf8;
