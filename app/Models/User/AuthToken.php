<?php

namespace App\Models\User;

use App\Models\User\Integration\Integration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class AuthToken
 * @package App\Models\User
 *
 * @property int $id
 * @property string $access_token
 * @property int $user_id
 * @property string $user_type
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 */
class AuthToken extends Model
{
    protected $table = 'auth_token';

    const USER_TYPE_DEALER = 'dealer';
    const USER_TYPE_DEALER_USER = 'dealer_user';
    const USER_TYPE_INTEGRATION = 'integration';

    /**
     * @var array
     */
    const USER_TYPES = [
        self::USER_TYPE_DEALER,
        self::USER_TYPE_DEALER_USER,
        self::USER_TYPE_INTEGRATION
    ];

    const INTEGRATION_ACCESS_TOKEN_LENGTH = 32;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'user_type',
        'access_token'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * Get User By Type
     *
     * @return HasOne
     */
    public function user(): HasOne
    {
        // Get Dealer User Instead?!
        if($this->user_type === 'dealer_user') {
            return $this->dealerUser();
        }

        if($this->user_type === 'integration') {
            return $this->integration();
        }

        // Return Dealer
        return $this->dealer();
    }

    /**
     * Get Dealer
     *
     * @return HasOne
     */
    public function dealer(): HasOne
    {
        return $this->hasOne(User::class, 'dealer_id', 'user_id');
    }

    /**
     * Get Dealer User
     *
     * @return HasOne
     */
    public function dealerUser(): HasOne
    {
        return $this->hasOne(DealerUser::Class, 'dealer_user_id', 'user_id');
    }

    /**
     * Get Interaction Integration
     *
     * @return HasOne
     */
    public function integration(): HasOne
    {
        return $this->hasOne(Integration::Class, 'id', 'user_id');
    }
}
