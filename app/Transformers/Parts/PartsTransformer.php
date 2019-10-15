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
             'vendor_id' => $part->vendor,
             'manufacturer_id' => $part->manufacturer,
             'brand_id' => $part->brand,
             'type_id' => $part->type,
             'category_id' => $part->category,
             'subcategory' => $part->subcategory,
             'title' => $part->title,
             'price' => (double)$part->price,
             'dealer_cost' => (double)$part->dealer_cost,
             'msrp' => (double)$part->msrp,
             'weight' => (double)$part->weight,
             'weight_rating' => (int)$part->weight_rating,
             'description' => $part->description,
             'qty' => (int)$part->qty,
             'show_on_website' => (bool)$part->show_on_website
         ];
    }
}