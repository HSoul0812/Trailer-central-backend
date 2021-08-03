<?php

namespace App\Models\CRM\Text;

use App\Models\CRM\Dealer\DealerLocation;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Text Number
 *
 * @package App\Models\CRM\Text
 */
class Number extends Model
{
    use TableAware;

    // Expiration time set to 120 hours
    const EXPIRATION_TIME = 120;

    /**
     * @var string
     */
    const TABLE_NAME = 'dealer_texts';

    protected $table = self::TABLE_NAME;

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

    /**
     * @return type
     */
    public function dealerLocation()
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_number', 'sms_phone');
    }
}