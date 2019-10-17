<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class AuthToken extends Model
{ 
    protected $table = 'auth_token';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
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
        return $this->hasOne('App\Models\User\User', 'dealer_id');
    }
}
