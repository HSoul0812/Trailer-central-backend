<?php

namespace App\Transformers\Marketing\Facebook;

use App\Models\Marketing\Facebook\Listings;
use App\Transformers\Inventory\InventoryTransformer;
use App\Transformers\Marketing\Facebook\MarketplaceTransformer;
use App\Transformers\Marketing\Facebook\ImageTransformer;
use League\Fractal\TransformerAbstract;

class ListingTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'images'
    ];

    /**
     * @var MarketplaceTransformer
     */
    protected $marketplaceTransformer;

    /**
     * @var InventoryTransformer
     */
    protected $inventoryTransformer;

    /**
     * @var ImageTransformer
     */
    protected $imageTransformer;

    public function __construct()
    {
        $this->marketplaceTransformer = new MarketplaceTransformer;
        $this->inventoryTransformer = new InventoryTransformer;
        $this->imageTransformer = new ImageTransformer;
    }

    public function transform(Listings $listing)
    {
        return [
            'id' => $listing->id,
            'marketplace' => $listing->marketplace ? $this->marketplaceTransformer->transform($listing->marketplace) : null,
            'inventory' => $listing->inventory ? $this->inventoryTransformer->transform($listing->inventory) : null,
            'facebook_id' => $listing->facebook_id,
            'account_type' => $listing->account_type,
            'page_id' => $listing->page_id,
            'listing_type' => $listing->listing_type,
            'specific_type' => $listing->specific_type,
            'year' => $listing->year,
            'price' => $listing->price,
            'make' => $listing->make,
            'model' => $listing->model,
            'description' => $listing->description,
            'location' => $listing->location,
            'color_exterior' => $listing->color_exterior,
            'color_interior' => $listing->color_interior,
            'trim' => $listing->trim,
            'mileage' => $listing->mileage,
            'body_style' => $listing->body_style,
            'condition' => $listing->condition,
            'transmission' => $listing->transmission,
            'fuel_type' => $listing->fuel_type,
            'status' => $listing->status,
            'created_at' => $listing->created_at,
            'updated_at' => $listing->updated_at
        ];
    }

    public function includeImages(Listings $listing)
    {
        return $this->collection($listing->images, $this->imageTransformer);
    }
}
