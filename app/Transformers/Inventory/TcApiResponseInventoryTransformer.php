<?php

declare(strict_types=1);

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;

class TcApiResponseInventoryTransformer extends TransformerAbstract
{
    public function transform($type): array
    {
        return [
             'id'               => (int) $type->id,
             'url'              => $type->url,
             'features'         => $type->features,
             'description'      => $type->description,
             'payload_capacity' => $type->payload_capacity,
             'gvwr'             => $type->gvwr,
             'weight'           => $type->weight,
             'length'           => $type->length,
             'manufacturer'     => $type->manufacturer,
             'dealer'           => $type->dealer,
             'listing_date'     => $type->listing_date,
             'price'            => $type->price,
             'sales_price'      => $type->sales_price,
             'inventory_title'  => $type->inventory_title,
             'photos'           => $type->photos,
         ];
    }
}
