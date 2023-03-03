<?php

namespace App\Models\Marketing\Craigslist;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Transaction
 *
 * @package App\Models\Marketing\Transaction
 */
class Transaction extends Model
{
    use TableAware;

    /** @var string */
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
     * Allowed types of transactions
     */
    public const TYPE_POST = 'post';
    public const TYPE_CREDIT = 'credit';
    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPES = [
      self::TYPE_POST,
      self::TYPE_CREDIT,
      self::TYPE_ADJUSTMENT,
    ];
}
