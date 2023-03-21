<?php

namespace App\Models\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Profile;
use App\Models\Marketing\Craigslist\Session;
use App\Models\Marketing\Craigslist\Queue;
use App\Models\Inventory\Inventory;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ActivePost
 * 
 * @package App\Models\Marketing\Craigslist
 */
class ActivePost extends Model
{
    use TableAware;


    // Define Table Name Constant
    const TABLE_NAME = 'clapp_active_posts';

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'clid',
        'inventory_id',
        'profile_id',
        'session_id',
        'queue_id',
        'added',
        'updated',
        'drafted',
        'posted',
        'response',
        'username',
        'title',
        'price',
        'area',
        'subarea',
        'category',
        'preview',
        'status',
        'view_url',
        'edit_url',
        'manage_url'
    ];

    /**
     * Get User
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->queue()->user;
    }

    /**
     * Get Profile
     * 
     * @return BelongsTo
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'id');
    }

    /**
     * Get Session
     * 
     * @return BelongsTo
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class, 'session_id', 'session_id');
    }

    /**
     * Get Queue
     * 
     * @return BelongsTo
     */
    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class, 'queue_id', 'queue_id');
    }

    /**
     * Get Parent Queue
     * 
     * @return BelongsTo
     */
    public function parentQueue(): BelongsTo
    {
        return $this->belongsTo(Queue::class, 'queue_id', 'parent_id');
    }

    /**
     * Get Inventory
     * 
     * @return BelongsTo
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }


    /**
     * Get Dealer ID From Related Items
     * 
     * @return int
     */
    public function getDealerIdAttribute(): int {
        // Find By Profile ID
        if(!empty($this->profile->dealer_id)) {
            return $this->profile->dealer_id;
        }

        // Find By Queue ID
        if(!empty($this->queue->dealer_id)) {
            return $this->queue->dealer_id;
        }

        // Find By Parent Queue ID
        if(!empty($this->parentQueue->dealer_id)) {
            return $this->parentQueue->dealer_id;
        }

        // Find By Session ID
        if(!empty($this->session->dealer_id)) {
            return $this->session->session_dealer_id;
        }

        // Can't Find One, Not Synced Correctly?
        return 0;
    }

    /**
     * Get Current Title
     * 
     * @return string
     */
    public function getCurrentTitleAttribute(): string {
        // Return Title From Inventory
        if(!empty($this->inventory) && !empty($this->inventory->title)) {
            return $this->inventory->title;
        }
        return $this->title ?? '';
    }

    /**
     * Get Current Stock
     * 
     * @return string
     */
    public function getCurrentStockAttribute(): string {
        // Return Stock From Inventory
        if(!empty($this->inventory) && !empty($this->inventory->stock)) {
            return $this->inventory->stock;
        }
        return '';
    }

    /**
     * Get Current Price
     * 
     * @return float
     */
    public function getCurrentPriceAttribute(): float {
        // Return Price From Inventory
        if(!empty($this->inventory) && !empty($this->inventory->price)) {
            return $this->inventory->price;
        }
        return $this->price ?? 0;
    }

    /**
     * Get Current Primary Image
     * 
     * @return string
     */
    public function getCurrentImageAttribute(): string {
        // Return Primary Image Inventory
        if(!empty($this->inventory) && !empty($this->inventory->primary_image)) {
            return $this->inventory->primary_image->image->filename;
        }
        return '';
    }
}