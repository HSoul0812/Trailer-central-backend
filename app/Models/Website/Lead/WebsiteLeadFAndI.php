<?php

namespace App\Models\Website\Lead;

use App\Models\CRM\Leads\Lead;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class WebsiteLeadFAndI
 *
 * @package App\Models\Website\Lead
 *
 * @property int fandi_id
 * @property int lead_id
 * @property string drivers_first_name
 * @property string drivers_mid_name
 * @property string drivers_last_name
 * @property string drivers_suffix
 * @property Carbon drivers_dob
 * @property string drivers_no
 * @property string drivers_front
 * @property string drivers_back
 * @property string ssn_no
 * @property string marital_status
 * @property string preferred_contact
 * @property string daytime_phone
 * @property string|null evening_phone
 * @property string|null mobile_phone
 * @property string rent_own
 * @property int monthly_rent
 * @property string type
 * @property string|null co_first_name
 * @property string|null co_last_name
 * @property string item_inquiry
 * @property string item_price
 * @property int down_payment
 * @property int|null trade_value
 * @property int|null trade_payoff
 * @property int|null other_income
 * @property string|null other_income_source
 * @property string|null extra
 * @property string|null preferred_salesperson
 * @property string delivery_method
 * @property string date_imported
 * @property Lead $lead
 */
class WebsiteLeadFAndI extends Model
{
    use TableAware;

    const TABLE_NAME = 'website_lead_fandi';

    const MARITAL_STATUS_SINGLE = 'single';
    const MARITAL_STATUS_MARRIED = 'married';
    const MARITAL_STATUS_DIVORCED = 'divorced';
    const MARITAL_STATUS_WIDOW = 'widow';

    const CONTACT_WAY_PHONE_DAYTIME = 'phone_daytime';
    const CONTACT_WAY_PHONE_EVENING = 'phone_evening';
    const CONTACT_WAY_PHONE_MOBILE = 'phone_mobile';
    const CONTACT_WAY_EMAIL = 'email';

    const TYPE_SINGLE = 'single';
    const TYPE_JOINT = 'joint';
    const TYPE_INDIVIDUAL = 'individual';

    const DELIVERY_METHOD_PICKUP = 'pickup';
    const DELIVERY_METHOD_RESIDENCE = 'delivery_residence';
    const DELIVERY_METHOD_ELSEWHERE = 'delivery_elsewhere';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'fandi_id';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'date_imported';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_id',
        'drivers_first_name',
        'drivers_mid_name',
        'drivers_last_name',
        'drivers_suffix',
        'drivers_dob',
        'drivers_no',
        'drivers_front',
        'drivers_back',
        'ssn_no',
        'marital_status',
        'preferred_contact',
        'daytime_phone',
        'evening_phone',
        'mobile_phone',
        'rent_own',
        'monthly_rent',
        'type',
        'co_first_name',
        'co_last_name',
        'item_inquiry',
        'item_price',
        'down_payment',
        'trade_value',
        'trade_payoff',
        'other_income',
        'other_income_source',
        'extra',
        'preferred_salesperson',
        'delivery_method',
        'date_imported',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'monthly_rent' => 'int',
        'down_payment' => 'int',
        'trade_value' => 'int',
        'trade_payoff' => 'int',
        'other_income' => 'int',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'date_imported',
        'drivers_dob',
    ];

    /**
     * Get Lead.
     *
     * @return BelongsTo
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id', 'identifier');
    }

    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }
}
