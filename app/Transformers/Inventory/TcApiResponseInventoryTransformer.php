<?php

declare(strict_types=1);

namespace App\Transformers\Inventory;

use App\DTOs\Inventory\TcApiResponseInventory;
use League\Fractal\TransformerAbstract;

class TcApiResponseInventoryTransformer extends TransformerAbstract
{
    public function transform(TcApiResponseInventory $type): array
    {
        return [
            'id'               => (int) $type->id,
            'url'              => $type->url,
            'features'         => $type->features,
            'attributes'       => $type->attributes,
            'description'      => $type->description,
            'payload_capacity' => $type->payload_capacity,
            'gvwr'             => $type->gvwr,
            'condition'        => $type->condition,
            'weight'           => $type->weight,
            'width'            => $type->width,
            'height'           => $type->height,
            'length'           => $type->length,
            'stock'            => $type->stock,
            'vin'              => $type->vin,
            'pull_type'        => $type->pull_type,
            'manufacturer'     => $type->manufacturer,
            'dealer'           => $type->dealer,
            'listing_date'     => $type->listing_date,
            'price'            => $type->price,
            'sales_price'      => $type->sales_price,
            'website_price'    => $type->website_price,
            'inventory_title'  => $type->inventory_title,
            'photos'           => $type->photos,
            'dealer_location'  => $type->dealer_location,
            'primary_image'    => $type->primary_image,
            'availability'     => $type->availability,
            'availability_label' => $type->availability_label,
            'is_archived'      => $type->is_archived,
            'type_id'          => $type->type_id,
            'type_label'       => $type->type_label,
            'category'         => $type->category,
         ];
    }
}
