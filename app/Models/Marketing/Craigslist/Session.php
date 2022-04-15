<?php

namespace App\Models\Marketing\Craigslist;

use App\Models\User\User;
use App\Models\Marketing\Craigslist\Profile;
use App\Models\Marketing\Craigslist\Queue;
use App\Models\Inventory\Inventory;
use App\Models\Traits\TableAware;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Session
 * 
 * @package App\Models\Marketing\Craigslist
 */
class Session extends Model
{
    use TableAware, Compoships;


    /**
     * @const int
     */
    const SLOT_EDITOR = 97;

    /**
     * @const int
     */
    const SLOT_SCHEDULER = 99;


    // Define Table Name Constant
    const TABLE_NAME = 'clapp_session';

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = 'session_row_id';

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
        'session_client',
        'session_scheduled',
        'session_started',
        'session_confirmed',
        'session_dealer_id',
        'session_slot_id',
        'session_profile_id',
        'session_last_activity',
        'webui_last_activity',
        'dispatch_last_activity',
        'sound_notify',
        'recoverable',
        'status',
        'state',
        'text_status',
        'nooped',
        'nooped_until',
        'queue_length',
        'last_item_began',
        'log',
        'market_code',
        'prev_url',
        'prev_url_skip',
        'sync_page_count',
        'sync_current_page',
        'ajax_url',
        'notify_error_init',
        'notify_error_timeout',
        'dismissed',
        'tz_offset'
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
     * Get Queue
     * 
     * @return HasMany
     */
    public function queue(): HasMany
    {
        return $this->hasMany(Queue::class, ['session_id', 'session_dealer_id', 'session_profile_id'],
                                ['session_id', 'dealer_id', 'profile_id']);
    }
}
