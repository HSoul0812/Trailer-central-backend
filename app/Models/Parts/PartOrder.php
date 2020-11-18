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
        'status',
        'fulfillment_type',
        'email_address',
        'phone_number',
        'shipto_name',
        'shipto_address',
        'shipto_city',
        'shipto_region',
        'shipto_postalcode',
        'shipto_country',
        'billto_name',
        'billto_address',
        'billto_city',
        'billto_region',
        'billto_postalcode',
        'billto_country',
        'cart_items',
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


    /**
     * Get Total
     */
    public function getTotalAttribute()
    {
        return round($this->subtotal + $this->tax + $this->shipping, 2);
    }

    /**
     * Get Ship To
     */
    public function getShipToAttribute()
    {
        // Get Name
        $shipto = !empty($this->shipto_name) ? $this->shipto_name : '';

        // Get City/State/Zip
        if(!empty($this->shipto_address)) {
            if(!empty($shipto)) {
                $shipto .= '<br>';
            }
            $shipto .= $this->shipto_address;
        }

        // Set City
        $cityState = '';
        if(!empty($this->shipto_city)) {
            $cityState .= $this->shipto_city;
        }
        if(!empty($this->shipto_region)) {
            if(!empty($cityState)) {
                $cityState .= ', ';
            }
            $cityState .= $this->shipto_region;
        }
        if(!empty($this->shipto_postal)) {
            if(!empty($cityState)) {
                $cityState .= ' ';
            }
            $cityState .= $this->shipto_postal;
        }

        // City State Exists?
        if(!empty($cityState)) {
            if(!empty($shipto)) {
                $shipto .= '<br>';
            }
            $shipto .= $cityState;
        }

        // Return Result
        return $shipto;
    }

    /**
     * Get Bill To
     */
    public function getBillToAttribute()
    {
        // Get Name
        $billto = !empty($this->billto_name) ? $this->billto_name : '';

        // Get City/State/Zip
        if(!empty($this->billto_address)) {
            if(!empty($billto)) {
                $billto .= '<br>';
            }
            $billto .= $this->billto_address;
        }

        // Set City
        $cityState = '';
        if(!empty($this->billto_city)) {
            $cityState .= $this->billto_city;
        }
        if(!empty($this->billto_region)) {
            if(!empty($cityState)) {
                $cityState .= ', ';
            }
            $cityState .= $this->billto_region;
        }
        if(!empty($this->billto_postal)) {
            if(!empty($cityState)) {
                $cityState .= ' ';
            }
            $cityState .= $this->billto_postal;
        }

        // City State Exists?
        if(!empty($cityState)) {
            if(!empty($billto)) {
                $billto .= '<br>';
            }
            $billto .= $cityState;
        }

        // Return Result
        return $billto;
    }
}