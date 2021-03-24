<?php


namespace App\Transformers\Quickbooks;


use App\Models\CRM\Dms\Quickbooks\Item;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(Item $item)
    {
        return [
            'id' => (int)$item->id,
            // 'dealer_id' => $item->dealer_id,
            'name' => $item->name,
            'description' => $item->description,
            'type' => $item->type,
            'item_primary_id' => $item->item_primary_id,
            'sku' => $item->sku,
            'item_category_id' => $item->item_category_id,
            'qty_on_hand' => $item->qty_on_hand,
            'unit_price' => $item->unit_price,
            'cost' => $item->cost,
            'vendor_id' => $item->vendor_id,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
            'qb_id' => $item->qb_id,
        ];
    }
}
