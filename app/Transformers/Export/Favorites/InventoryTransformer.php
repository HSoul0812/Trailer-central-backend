<?php

namespace App\Transformers\Export\Favorites;

use App\Models\Inventory\Inventory;

class InventoryTransformer
{
    public function transform(Inventory $inventory): array
    {
        return [
            'stock' => $inventory->stock,
            'vin' => $inventory->vin,
            'location' => $inventory->dealerLocation->name,
            'condition' => $inventory->condition,
            'type' => $inventory->entityType->title,
            'category' => $inventory->category,
            'title' => $inventory->title,
            'year' => $inventory->year,
            'manufacturer' => $inventory->manufacturer,
            'status' => $inventory->status,
            'msrp' => $inventory->msrp,
            'model' => $inventory->model,
            'price' => $inventory->price,
            'sales_price' => $inventory->sales_price,
            'hidden_price' => $inventory->hidden_price
        ];
    }
}
