<?php

namespace App\Transformers\Inventory;

use App\DTOs\Inventory\Inventory;
use JetBrains\PhpStorm\ArrayShape;
use League\Fractal\TransformerAbstract;

class InventoryTransformer extends TransformerAbstract
{
    #[ArrayShape([
        'id' => 'string',
        'isActive' => 'bool',
        'dealerId' => 'string',
        'dealerLocationId' => 'string',
        'createdAt' => 'string',
        'updatedAt' => 'string',
        'updatedAtUser' => 'string',
        'isSpecial' => 'bool',
        'isFeatured' => 'bool',
        'isArchived' => 'bool',
        'stock' => 'string',
        'title' => 'string',
        'year' => 'string',
        'manufacturer' => 'string',
        'model' => 'string',
        'description' => 'string',
        'status' => 'string',
        'category' => 'string',
        'useWebsitePrice' => 'bool',
        'condition' => 'string',
        'length' => 'string',
        'width' => 'string',
        'height' => 'string',
        'showOnKsl' => 'bool',
        'showOnRacingjunk' => 'bool',
        'showOnWebsite'=> 'bool',
        'dealer' => ['name' => 'string', 'email' => 'string'],
        'location' => [
            'name' => 'string',
            'email' => 'string',
            'contact' => 'string',
            'website' => 'string',
            'phone' => 'string',
            'address' => 'string',
            'city' => 'string',
            'region' => 'string',
            'postalCode' => 'string',
            'country' => 'string',
            'geo' => ['lat' => 'string', 'lon' => 'string']
        ],
        'widthInches' => 'string',
        'heightInches' => 'string',
        'lengthInches' => 'string',
        'widthDisplayMode' => 'string',
        'heightDisplayMode' => 'string',
        'lengthDisplayMode' => 'string',
        'keywords' => 'array',
        'availability' => 'string',
        'availabilityLabel' => 'string',
        'typeLabel' => 'string',
        'categoryLabel' => 'string',
        'basicPrice' => 'string',
        'originalWebsitePrice' => 'string',
        'websitePrice' => 'string',
        'existingPrice' => 'string',
        'numAxles' => 'string',
        'frameMaterial' => 'string',
        'pullType' => 'string',
        'numStalls' => 'string',
        'loadType' => 'string',
        'roofType' => 'string',
        'noseType' => 'string',
        'color' => 'string',
        'numSleeps' => 'string',
        'numAc' => 'string',
        'fuelType' => 'string',
        'isRental' => 'bool',
        'numSlideouts' => 'string',
        'numBatteries' => 'string',
        'horsepower' => 'string',
        'numPassengers' => 'string',
        'conversion' => 'string',
        'cabType' => 'string',
        'engineSize' => 'string',
        'transmission' => 'string',
        'driveTrail' => 'string',
        'floorplan' => 'string',
        'propulsion' => 'string',
        'featureList' => 'array',
        'image' => 'string',
        'images' => 'array',
        'imagesSecondary' => 'array'
    ])]
    public function transform(Inventory $inventory): array {
        return $inventory->toArray();
    }
}
