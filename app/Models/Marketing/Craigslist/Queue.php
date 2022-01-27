<?php

namespace App\Models\Marketing\Craigslist;

use App\Models\User\User;
use App\Models\Marketing\Craigslist\Profile;
use App\Models\Marketing\Craigslist\Session;
use App\Models\Inventory\Inventory;
use App\Models\Traits\TableAware;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Queue
 * 
 * @package App\Models\Marketing\Craigslist
 */
class Queue extends Model
{
    use TableAware, Compoships;


    // Define Table Name Constant
    const TABLE_NAME = 'clapp_queue';

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = 'queue_id';

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
        'parent_id',
        'time',
        'command',
        'parameter',
        'dealer_id',
        'profile_id',
        'inventory_id',
        'status',
        'state',
        'img_status'
    ];

    /**
     * Get User
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
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
     * Get Inventory
     * 
     * @return BelongsTo
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }

    /**
     * Get Session
     * 
     * @return BelongsTo
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class, ['session_id', 'dealer_id', 'profile_id'],
                                ['session_id', 'session_dealer_id', 'session_profile_id']);
    }
}
