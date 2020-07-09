<?php

namespace App\Models\CRM\Text;

use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Dealer\DealerLocation;

/**
 * Class Text Number
 *
 * @package App\Models\CRM\Text
 */
class Number extends Model
{
    protected $table = 'dealer_texts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_number',
        'customer_number',
        'twilio_number',
        'customer_name',
        'expiration_time'
    ];

    /**
     * @return type
     */
    public function dealerLocation()
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_number', 'sms_phone');
    }

    /**
     * Get Active Twilio Number
     * 
     * @param type $dealerNo
     * @param type $customerNo
     * @return array
     */
    public static function getActiveTwilioNumber($dealerNo, $customerNo) {
        return self::get(['twilio_number'])
            ->where('dealer_number', $dealerNo)
            ->where('customer_number', $customerNo)
            ->first();
    }

    /**
     * Find Twilio Numbers for Dealer and Customer
     * 
     * @param type $dealerNo
     * @param type $customerNo
     */
    public function findTwilioNumbers($dealerNo, $customerNo) {
        return self::get(['twilio_number'])
            ->where('dealer_number', $dealerNo)
            ->orWhere('customer_number', $customerNo)
            ->all();
    }
}