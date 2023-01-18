<?php

namespace App\Transformers\Inventory;

use App\DTOs\Inventory\TcEsInventory;
use JetBrains\PhpStorm\ArrayShape;
use League\Fractal\TransformerAbstract;

class InventoryTransformer extends TransformerAbstract
{
    #[ArrayShape([
        'id' => 'string',
        'is_active' => 'bool',
        'dealer_id' => 'string',
        'dealer_location_id' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string',
        'updated_at_user' => 'string',
        'is_special' => 'bool',
        'is_featured' => 'bool',
        'is_archived' => 'bool',
        'stock' => 'string',
        'title' => 'string',
        'year' => 'string',
        'manufacturer' => 'string',
        'model' => 'string',
        'description' => 'string',
        'status' => 'string',
        'category' => 'string',
        'use_website_price' => 'bool',
        'condition' => 'string',
        'length' => 'string',
        'width' => 'string',
        'height' => 'string',
        'show_on_ksl' => 'bool',
        'show_on_racingjunk' => 'bool',
        'show_on_website'=> 'bool',
        'dealer' => ['name' => 'string', 'email' => 'string', 'is'],
        'location' => [
            'name' => 'string',
            'email' => 'string',
            'contact' => 'string',
            'website' => 'string',
            'phone' => 'string',
            'address' => 'string',
            'city' => 'string',
            'region' => 'string',
            'postal_code' => 'string',
            'country' => 'string',
            'geo' => ['lat' => 'string', 'lon' => 'string']
        ],
        'width_inches' => 'string',
        'height_inches' => 'string',
        'length_inches' => 'string',
        'width_display_mode' => 'string',
        'height_display_mode' => 'string',
        'length_display_mode' => 'string',
        'keywords' => 'array',
        'availability' => 'string',
        'availability_label' => 'string',
        'type_label' => 'string',
        'category_label' => 'string',
        'basic_price' => 'string',
        'original_website_price' => 'string',
        'website_price' => 'string',
        'existing_price' => 'string',
        'num_axles' => 'string',
        'frame_material' => 'string',
        'pull_type' => 'string',
        'num_stalls' => 'string',
        'load_type' => 'string',
        'roof_type' => 'string',
        'nose_type' => 'string',
        'color' => 'string',
        'num_sleeps' => 'string',
        'num_ac' => 'string',
        'fuel_type' => 'string',
        'is_rental' => 'bool',
        'num_slideouts' => 'string',
        'num_batteries' => 'string',
        'horsepower' => 'string',
        'num_passengers' => 'string',
        'conversion' => 'string',
        'cab_type' => 'string',
        'engine_size' => 'string',
        'transmission' => 'string',
        'drive_trail' => 'string',
        'floorplan' => 'string',
        'propulsion' => 'string',
        'feature_list' => 'array',
        'image' => 'string',
        'images' => 'array',
        'images_secondary' => 'array'
    ])]
    public function transform(TcEsInventory $inventory): array {
        return $inventory->toArray();
    }
}
