<?php

namespace App\Models\Parts;

use Illuminate\Database\Eloquent\Model;
use App\Models\Parts\Part;
use App\Models\Website\Website;

/**
 * Class PartOrder
 *
 * @package App\Models\Parts
 */
class PartOrder extends Model
{

    const TABLE_NAME = 'part_orders';

    /**
     * @const array
     */
    const STATUS_FIELDS = [
        'abandoned',
        'unfulfilled',
        'pending',
        'dropshipped',
        'fulfilled'
    ];

    /**
     * @const array
     */
    const FULFILLMENT_TYPES = [
        'manually' => 0,
        'dropship' => 1,
        'review'   => 2
    ];

    /**
     * Set Table Name
     *
     * @var type 
     */
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'website_id',
        'shipto_name',
        'shipto_address',
        'cart_items',
        'status',
        'fulfillment',
        'email_address',
        'phone_number',
        'subtotal',
        'tax',
        'shipping',
        'order_key'
    ];

    /**
     * @return BelongsTo
     */
    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * @return BelongsTo
     */
    public function website()
    {
        return $this->belongsTo(Website::class);
    }
}