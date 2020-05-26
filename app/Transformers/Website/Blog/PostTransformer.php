<?php

namespace App\Transformers\Website\Blog;

use League\Fractal\TransformerAbstract;
use App\Models\Website\Blog\Post;

class PostTransformer extends TransformerAbstract
{
    public function transform(Post $post)
    {
	 return [
             'id' => (int)$post->id,
             'dealer_id' => (int)$post->dealer_id,
             'vendor' => $post->vendor,
             'manufacturer' => $post->manufacturer,
             'brand' => $post->brand,
             'type' => $post->type,
             'category' => $post->category,
             'sku' => $post->sku,
             'stock' => $post->sku,
             'subcategory' => $post->subcategory,
             'title' => $post->title,
             'alternative_part_number' => $post->alternative_part_number,
             'price' => (double)$post->price,
             'dealer_cost' => (double)$post->dealer_cost,
             'msrp' => (double)$post->msrp,
             'weight' => (double)$post->weight,
             'weight_rating' => $post->weight_rating,
             'description' => $post->description,
             'qty' => (int)$post->qty,
             'show_on_website' => (bool)$post->show_on_website,
             'is_vehicle_specific' => (bool)$post->is_vehicle_specific,
             'images' => $post->images->pluck('image_url'),
             'vehicle_specific' => $post->vehicleSpecific,
             'video_embed_code' => $post->video_embed_code,
             'stock_min' => $post->stock_min,
             'stock_max' => $post->stock_max,
             'bins' => $post->bins
         ];
    }
}
