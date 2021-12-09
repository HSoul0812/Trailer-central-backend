<?php

namespace App\Models\Marketing\Facebook;

use App\Models\Inventory\Image as InventoryImage;
use App\Models\Marketing\Facebook\Listings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Image
 * 
 * @package App\Models\Marketing\Facebook\Image
 */
class Image extends Model
{
    // Define Table Name Constant
    const TABLE_NAME = 'fbapp_listings_images';


    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'listing_id',
        'image_id',
        'position'
    ];

    /**
     * Get Marketplace Listing
     * 
     * @return BelongsTo
     */
    public function listings(): BelongsTo
    {
        return $this->belongsTo(Listings::class, 'listing_id', 'id');
    }

    /**
     * Get Inventory Image
     * 
     * @return BelongsTo
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(InventoryImage::class, 'image_id', 'image_id');
    }
}