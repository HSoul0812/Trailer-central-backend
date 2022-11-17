<?php

namespace App\Services\Marketing\Craigslist\DTOs;

use App\Models\Inventory\Inventory;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use App\Traits\S3\S3Helper;

/**
 * Class InventoryFacebook
 * 
 * @package App\Services\Marketing\Craigslist\DTOs
 */
class ClappInventory
{
    use WithConstructor, WithGetter, S3Helper;

    /**
     * @const string Craigslist Type Scheduler
     */
    const CLAPP_TYPE_SCHEDULER = 'scheduler';

    /**
     * @const string Craigslist Type Poster
     */
    const CLAPP_TYPE_POSTER = 'poster';

    /**
     * @const string Craigslist Type Archives
     */
    const CLAPP_TYPE_ARCHIVES = 'archives';

    /**
     * @const string Craigslist Type Default
     */
    const CLAPP_TYPE_DEFAULT = self::CLAPP_TYPE_SCHEDULER;

    /**
     * @const array<string> Craigslist Types Array
     */
    const CLAPP_TYPES = [
        self::CLAPP_TYPE_SCHEDULER,
        self::CLAPP_TYPE_POSTER,
        self::CLAPP_TYPE_ARCHIVES,
    ];


    /**
     * @const string Link Notice Category
     */
    const CLAPP_LINK_NOTICE = 'NOTICE';


    /**
     * @var int
     */
    private $inventoryId;

    /**
     * @var int
     */
    private $locationId;

    /**
     * @var string
     */
    private $stock;

    /**
     * @var string
     */
    private $title;

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
     * @var string
     */
    private $type;


    /**
     * Create InventoryFacebook From Inventory
     * 
     * @param \stdclass $inventory
     * @param null|string $type
     * @return InventoryFacebook
     */
    public static function fill(\stdclass $inventory, ?string $type): ClappInventory
    {
        // Create Inventory Mapping
        return new self([
            'inventory_id' => $inventory->inventory_id,
            'location_id' => $inventory->dealer_location_id,
            'stock' => $inventory->stock,
            'title' => $inventory->title,
            'category' => $inventory->category,
            'manufacturer' => $inventory->manufacturer,
            'price' => $inventory->price,
            'status' => $inventory->cl_status,
            'primary_image' => $inventory->primary_image ?? $inventory->primary_image_backup,
            'last_posted' => $inventory->added,
            'next_scheduled' => $inventory->session_scheduled,
            'queue_id' => $inventory->queue_id,
            'craigslist_id' => $inventory->clid,
            'view_url' => $inventory->view_url,
            'manage_url' => $inventory->manage_url,
            'type' => $type ?? self::CLAPP_TYPE_DEFAULT
        ]);
    }

    /**
     * Get Primary Image
     * 
     * @return string
     */
    public function getPrimaryImage(): string {
        if($this->primaryImage) {
            return $this->getS3Url($this->primaryImage);
        }
        return '';
    }

    /**
     * Is Scheduler?
     * 
     * @return bool
     */
    public function isScheduler(): int {
        if($this->type && $this->type === self::CLAPP_TYPE_SCHEDULER) {
            return true;
        }
        return false;
    }
}