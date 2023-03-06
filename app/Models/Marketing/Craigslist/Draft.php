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
 * Class Draft
 * 
 * @package App\Models\Marketing\Craigslist
 */
class Draft extends Model
{
    use TableAware;


    // Define Table Name Constant
    const TABLE_NAME = 'clapp_drafts';

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
        'session_id',
        'queue_id',
        'inventory_id',
        'profile_id',
        'username',
        'response',
        'added',
        'drafted',
        'title',
        'price',
        'category',
        'area',
        'subarea',
        'preview'
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
     * Get Inventory
     * 
     * @return BelongsTo
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
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
}
