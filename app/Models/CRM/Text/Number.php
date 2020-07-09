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

    // No Timestamps
    public $timestamps = false;

    // Expiration time set to 120 hours
    const EXPIRATION_TIME = 120;

    /**
     * @return type
     */
    public function dealerLocation()
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_number', 'sms_phone');
    }

    /**
     * Set Phone as Used
     * 
     * @param string $dealerNo
     * @param string $usedNo
     * @param string $customerNo
     * @param string $customerName
     * @return Number
     */
    public static function setPhoneAsUsed($dealerNo, $usedNo, $customerNo, $customerName) {
        // Calculate Expiration
        $expirationTime = time() + (self::EXPIRATION_TIME * 60 * 60);

        // Create Number in DB
        return self::create([
            'dealer_number'   => $dealerNo,
            'twilio_number'   => $usedNo,
            'customer_number' => $customerNo,
            'customer_name'   => $customerName,
            'expiration_time' => $expirationTime
        ]);
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
            ->first()->twilio_number;
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