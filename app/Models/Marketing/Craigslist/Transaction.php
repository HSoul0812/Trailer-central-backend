<?php

namespace App\Models\Marketing\Craigslist;

use App\Models\User\User;
use App\Models\User\DealerClapp;
use App\Models\Marketing\Craigslist\Balance;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Transaction
 *
 * 
 * @package App\Models\Marketing\Craigslist
 */
class Transaction extends Model
{
    use TableAware;

    /**
     * @const string Type Post
     * @const string Type Debut
     * @const string Type Adjustment
     */
    public const TYPE_POST = 'post';
    public const TYPE_CREDIT = 'credit';
    public const TYPE_ADJUST = 'adjustment';

    /**
     * @const array<string>
     */
    public const TYPES = [
      self::TYPE_POST,
      self::TYPE_CREDIT,
      self::TYPE_ADJUST,
    ];


    // Define Table Name Constant
    const TABLE_NAME = 'clapp_transaction';

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = 'clapp_txn_id';

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
        'dealer_id',
        'ip_addr',
        'user_agent',
        'session_id',
        'queue_id',
        'inventory_id',
        'amount',
        'balance',
        'type',
    ];

    /**
     * Get Dealer
     *
     * @return BelongsTo
     */
    public function dealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get DealerClapp
     * 
     * @return BelongsTo
     */
    public function dealerClapp(): BelongsToe
    {
        return $this->belongsTo(DealerClapp::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get Balance
     *
     * @return BelongsTo
     */
    public function balance(): BelongsTo
    {
        return $this->belongsTo(Balance::class, 'dealer_id', 'dealer_id');
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
}