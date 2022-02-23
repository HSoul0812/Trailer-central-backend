<?php

namespace App\Models\CRM\Text;

use App\Models\CRM\Dealer\DealerLocation;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Text Number Verify
 *
 * @package App\Models\CRM\Text
 */
class NumberVerify extends Model
{
    use TableAware;

    /**
     * @var string
     */
    const TABLE_NAME = 'dealer_texts_verify';

    protected $table = self::TABLE_NAME;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_number',
        'twilio_number',
        'verify_type'
    ];

    /**
     * @return type
     */
    public function dealerLocation()
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_number', 'sms_phone');
    }
}