<?php

namespace App\Models\CRM\Text;

use App\Models\Traits\TableAware;
use App\Models\User\DealerLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Text Number
 *
 * @package App\Models\CRM\Text
 *
 * @property int $id
 * @property string $dealer_number
 * @property string $customer_number
 * @property string $twilio_number
 * @property string $customer_name
 * @property int $expiration_time
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
     * @return BelongsTo
     */
    public function dealerLocation(): BelongsTo
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_number', 'sms_phone');
    }
}
