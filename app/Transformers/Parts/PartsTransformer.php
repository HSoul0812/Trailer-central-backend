<?php

namespace App\Transformers\Parts;

use League\Fractal\TransformerAbstract;
use App\Models\Parts\Part;

class PartsTransformer extends TransformerAbstract
{
    public function transform(Part $part)
    {
	 return [
             'id' => (int)$part->id,
             'dealer_id' => (int)$part->dealer_id,
             'vendor' => $part->vendor,
             'manufacturer' => $part->manufacturer,
             'brand' => $part->brand,
             'type' => $part->type,
             'category' => $part->category,
             'sku' => $part->sku,
             'stock' => $part->sku,
             'subcategory' => $part->subcategory,
             'title' => $part->title,
             'alternative_part_number' => $part->alternative_part_number,
             'price' => (double)number_format((double)$part->modified_cost, 2, '.', ''),
             'dealer_cost' => (double)$part->dealer_cost,
             'msrp' => (double)$part->msrp,
             'shipping_fee' => (double) $part->shipping_fee,
             'use_handling_fee' => (bool) $part->use_handling_fee,
             'handling_fee' => (double) $part->handling_fee,
             'website_fee' => (double) $part->website_fee,
             'fullfillment_type' => (int) $part->fulfillment_type,
             'weight' => (double)$part->weight,
             'weight_rating' => $part->weight_rating,
             'description' => $part->description,
             'qty' => (int)$part->qty,
             'show_on_website' => (bool)$part->show_on_website,
             'is_vehicle_specific' => (bool)$part->is_vehicle_specific,
             'images' => $part->images->pluck('image_url'),
             'vehicle_specific' => $part->vehicleSpecific,
             'video_embed_code' => $part->video_embed_code,
             'stock_min' => $part->stock_min,
             'stock_max' => $part->stock_max,
             'bins' => $part->bins
         ];
    }
}
