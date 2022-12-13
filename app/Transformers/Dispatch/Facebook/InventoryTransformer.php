<?php

namespace App\Transformers\Dispatch\Facebook;

use App\Services\Dispatch\Facebook\DTOs\InventoryFacebook;
use App\Transformers\Dispatch\Facebook\ImageTransformer;
use League\Fractal\TransformerAbstract;

class InventoryTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'images'
    ];

    public function __construct(ImageTransformer $imageTransformer) {
        $this->imageTransformer = $imageTransformer;
    }

    public function transform(InventoryFacebook $inventory)
    {
        return [
            'inventory_id' => $inventory->inventoryId,
            'facebook_id' => $inventory->facebookId ?: null,
            'account_type' => $inventory->getAccountType(),
            'page_url' => $inventory->pageUrl,
            'listing_type' => $inventory->getListingType(),
            'specific_type' => $inventory->getSpecificType(),
            'price' => $inventory->price,
            'year' => $inventory->year,
            'make' => $inventory->manufacturer,
            'model' => $inventory->model,
            'description' => $inventory->getPlainDescription(),
            'location' => $inventory->location,
            'color_external' => $inventory->getColor(),
            'color_internal' => $inventory->getColor(true),
            'mileage' => $inventory->mileage,
            'body_style' => $inventory->getBodyStyle(),
            'condition' => $inventory->getCondition(),
            'transmission' => $inventory->getTransmission(),
            'fuel_type' => $inventory->getFuelType()
        ];
    }

    public function includeImages(InventoryFacebook $inventory)
    {
        // Let's make sure all images have filenames.
        $imagesWithFileName = $inventory->images->filter(function ($inventoryImage) {
            return isset($inventoryImage->image) && $inventoryImage->image->filename;
        });

        if($imagesWithFileName) {
            return $this->collection(
                $imagesWithFileName,
                $this->imageTransformer
            );
        }
        return $this->null();
    }
}