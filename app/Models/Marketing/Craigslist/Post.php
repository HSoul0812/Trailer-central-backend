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
 * Class Post
 * 
 * @package App\Models\Marketing\Craigslist
 */
class Post extends Model
{
    use TableAware;


    // Define Table Name Constant
    const TABLE_NAME = 'clapp_posts';

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
        'inventory_id',
        'session_id',
        'queue_id',
        'username',
        'renews',
        'response',
        'added',
        'drafted',
        'posted',
        'profile_id',
        'title',
        'price',
        'area',
        'subarea',
        'category',
        'preview',
        'clid',
        'renewable',
        'cl_status',
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
     * Get Inventory
     * 
     * @return BelongsTo
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }
}