<?php

namespace App\Models\User;

use App\Models\Inventory\Inventory;
use App\Models\User\NewDealerUser;
use App\Models\User\Dealer;
use App\Models\CRM\Text\Number;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\DealerLocationSalesTax;

class DealerLocation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dealer_location';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'dealer_location_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "dealer_id",
        "is_default",
        "is_default_for_invoice",
        "name",
        "contact",
        "website",
        "phone",
        "location_id" 
        // TODO: Add fields
    ];

    /**
     * @return type
     */
    public function dealer()
    {
        return $this->belongsTo(NewDealerUser::class, 'dealer_id', 'id');
    }

    /**
     * @return type
     */
    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'dealer_location_id', 'dealer_location_id');
    }

    /**
     * @return type
     */
    public function salesTax()
    {
        return $this->hasOne(DealerLocationSalesTax::class, 'dealer_location_id', 'dealer_location_id');
    }

    /**
     * @return type
     */
    public function number()
    {
        return $this->belongsTo(Number::class, 'sms_phone', 'dealer_number');
    }
}
