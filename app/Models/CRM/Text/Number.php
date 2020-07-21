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
}