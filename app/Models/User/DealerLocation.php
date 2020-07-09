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


    /**
     * Get All Dealer Numbers
     * 
     * @param int $dealerId
     * @return type
     */
    public static function findAllDealerNumbers($dealerId)
    {
        return self::get(['sms_phone', 'dealer_location_id'])
            ->whereDealerId($dealerId)
            ->whereNotNull('sms_phone')
            ->all();
    }

    /**
     * Get Dealer Number for Location or Default
     * 
     * @param int $dealerId
     * @param int $locationId
     * @return type
     */
    public static function findDealerNumber($dealerId, $locationId) {
        // Get Dealer Location
        $location = self::find($locationId)->first();

        // Get Numbers By Dealer ID
        if(!empty($location->dealer_id)) {
            $numbers = self::findAllDealerNumbers($location->dealer_id);
        } else {
            $numbers = self::findAllDealerNumbers($dealerId);
        }

        // Loop Numbers
        $phoneNumber = '';
        if(!empty($numbers) && count($numbers) > 0) {
            foreach($numbers as $number) {
                // Set Correct Location's Phone Number
                if($locationId == $number->dealer_location_id) {
                    $phoneNumber = $number->sms_phone;
                }
            }

            // Still No Valid Number?!
            if(empty($phoneNumber)) {
                foreach($numbers as $number) {
                    $phoneNumber = $number->sms_phone;
                    break;
                }
            }
        }

        // Return Phone Number
        return $phoneNumber;
    }
}
