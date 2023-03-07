<?php

namespace App\Transformers\Parts\Textrail;

use App\Models\Parts\Textrail\Part;
use App\Transformers\Parts\PartAttributeTransformer;
use App\Transformers\Parts\PartsTransformerInterface;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class PartsTransformer extends TransformerAbstract implements PartsTransformerInterface
{
    protected $availableIncludes = [
        'partAttributes'
    ];


    public function transform(Part $part): array
    {
	 return [
             'id' => (int)$part->id,
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
             'price' => (double)number_format((double)$part->price, 2, '.', ''),
             'dealer_cost' => (double)$part->dealer_cost,
             'latest_cost' => (double)$part->latest_cost,
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
             'images' => $part->images->pluck('image_url')
         ];
    }

    /**
     * Include part attributes.
     *
     * @param \App\Models\Parts\Part $part
     * @return Collection
     */
    public function includePartAttributes(Part $part): Collection
    {
        return $this->collection(
            $part->partAttributes,
            new PartAttributeTransformer()
        );
    }
}
