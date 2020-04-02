<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class CrmUser extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'new_crm_user';

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

    public function dealer()
    {
        return $this->hasOne(Dealer::class, 'user_id', 'user_id');
    }
}
