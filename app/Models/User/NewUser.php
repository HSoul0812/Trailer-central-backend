<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

/**
 * Class NewUser
 * @package App\Models\User
 *
 * @property int $user_id
 * @property string $username
 * @property string $email
 * @property string $password
 */
class NewUser extends Model
{
    const TABLE_NAME = 'new_user';

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
    protected $primaryKey = "user_id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "username",
        "email",
        "password"
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

    /**
     * Get the CRM user
     */
    public function crmUser()
    {
        return $this->belongsTo(CrmUser::class, 'user_id', 'user_id');
    }

    public static function getTableName() {
        return self::TABLE_NAME;
    }


    /**
     * Get Dealer Credentials For Specified User
     *
     * @return type
     */
    public static function getDealerCredential($userId, $salesPersonId = 0) {
        // Get User By ID
        $user = self::findOrFail($userId);
        $dealerCredentials = array(
            'email' => $user->email,
            'password' => $user->password
        );

        // Get Secondary User Status
        if(!empty($salesPersonId)) {
            $dealerUser = DealerUser::getBySalesPerson($salesPersonId);
            if(!empty($dealerUser)) {
                $dealerCredentials['is_sales_person'] = true;
                $dealerCredentials['sales_person_email'] = $dealerUser->email;
                $dealerCredentials['secondary_id'] = $dealerUser->dealer_user_id;
            }
        }

        // Return Dealer Credential
        return urlencode(base64_encode(json_encode($dealerCredentials)));
    }
}
