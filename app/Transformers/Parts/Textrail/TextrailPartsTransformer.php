<?php

namespace App\Transformers\Parts\Textrail;

use App\Models\Parts\Textrail\Part;
use League\Fractal\TransformerAbstract;

class TextrailPartsTransformer extends TransformerAbstract 
{
    public function transform($part): array
    {
	 return [
             'id' => (int)$part->id,
             'manufacturer_id' => $part->manufacturer_id,
             'brand_id' => $part->brand_id,
             'type_id' => $part->type_id,
             'category_id' => $part->category_id,
             'sku' => $part->sku,
             'title' => $part->name,
             'price' => $part->price,
             'weight' => $part->weight ? $part->weight : '',
             'description' => $part->description,
         ];
    }
}