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
    
    public function user()
    {
        return $this->hasOne(User::class, 'dealer_id', 'user_id');
    }
    
    public function dealerUser()
    {
        return $this->hasOne(DealerUser::Class, 'dealer_user_id', 'user_id');
    }
}
