<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class AuthToken extends Model
{ 
    protected $table = 'auth_token';

    /**
     * @var array
     */
    const USER_TYPES = [
        'dealer',
        'dealer_user'
    ];
    
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
    public function user()
    {
        // Get Dealer User Instead?!
        if($this->user_type === 'dealer_user') {
            return $this->dealerUser();
        }

        // Return Dealer
        return $this->dealer();
    }

    /**
     * Get Dealer
     * 
     * @return HasOne
     */
    public function dealer() {
        return $this->hasOne(User::class, 'dealer_id', 'user_id');
    }

    /**
     * Get Dealer User
     * 
     * @return HasOne
     */
    public function dealerUser()
    {
        return $this->hasOne(DealerUser::Class, 'dealer_user_id', 'user_id');
    }
}
