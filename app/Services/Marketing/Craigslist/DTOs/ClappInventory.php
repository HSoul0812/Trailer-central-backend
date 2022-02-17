<?php

namespace App\Services\Marketing\Craigslist\DTOs;

use App\Models\Inventory\Inventory;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class InventoryFacebook
 * 
 * @package App\Services\Marketing\Craigslist\DTOs
 */
class ClappInventory
{
    use WithConstructor, WithGetter;


    /**
     * @var int
     */
    private $inventoryId;

    /**
     * @var string
     */
    private $stock;

    /**
     * @var string
     */
    private $category;

    /**
     * @var string
     */
    private $manufacturer;

    /**
     * @var float
     */
    private $price;

    /**
     * @var string
     */
    private $primaryImage;


    /**
     * @var int
     */
    private $queueId;

    /**
     * @var string
     */
    private $craigslistId;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $lastPosted;

    /**
     * @var string
     */
    private $nextScheduled;

    /**
     * @var string
     */
    private $viewUrl;

    /**
     * @var string
     */
    private $manageUrl;


    /**
     * Create InventoryFacebook From Inventory
     * 
     * @param \stdclass $inventory
     * @return InventoryFacebook
     */
    public static function fill(\stdclass $inventory): InventoryFacebook
    {
        // Create Inventory Mapping
        return new self([
            'inventory_id' => $inventory->inventory_id,
            'stock' => $inventory->stock,
            'title' => $inventory->title,
            'category' => $inventory->category,
            'manufacturer' => $inventory->manufacturer,
            'price' => $inventory->price,
            'status' => $inventory->cl_status,
            'primary_image' => $inventory->primary_image,
            'last_posted' => $inventory->added,
            'next_scheduled' => $inventory->session_scheduled,
            'queue_id' => $inventory->queue_id,
            'craigslist_id' => $inventory->clid,
            'view_url' => $inventory->view_url,
            'manage_url' => $inventory->manage_url
        ]);
    }

    /**
     * Get Primary Image
     * 
     * @return string
     */
    public function getPrimaryImage(): string {
        if($this->primaryImage) {
            return config('app.cdn_url') . $this->primaryImage;
        }
        return '';
    }
}