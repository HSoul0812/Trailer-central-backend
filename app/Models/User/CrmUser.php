<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\User\Settings;

class CrmUser extends Model
{
    public const TABLE_NAME = 'new_crm_user';

    public const STATUS_ACTIVE = 1;

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
    protected $primaryKey = "id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "user_id",
        "logo",
        "first_name",
        "last_name",
        "display_name",
        "state",
        "dealer_name",
        "active",
        "price_per_mile",
        "email_signature",
        "timezone",
        "enable_hot_potato",
        "disable_daily_digest",
        "enable_assign_notification",
        "enable_due_notification",
        "enable_past_notification",
        "is_factory",
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    public static function getTableName() {
        return self::TABLE_NAME;
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute() {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get Dealer Timezone
     *
     * @return string
     */
    public function getDealerTimezoneAttribute(): string {
        return $this->timezone ?: env('DB_TIMEZONE');
    }


    /**
     * Get the NewDealerUser
     */
    public function newDealerUser()
    {
        return $this->belongsTo(NewDealerUser::class, 'user_id', 'user_id');
    }
    
    public function settings()
    {
        return $this->hasMany(Settings::class, 'user_id', 'user_id');
    }
}
