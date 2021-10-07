<?php

namespace App\Models\Website;

use App\Models\Website\Website;
use Illuminate\Database\Eloquent\Model;

class DealerWebsiteUser extends Model
{
    //
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'website_id',
    ];

    protected $table = 'dealer_website_user';
    public function token() {
        return $this->hasOne(DealerWebsiteUserToken::class, 'dealer_website_user_id', 'id');
    }

    public function website() {
        return $this->belongsTo(Website::class, 'website_id');
    }

    public function setPasswordAttribute($value) {
        $this->attributes['password'] = \Hash::make($value);
    }

    public function checkPassword($password) {
        return \Hash::check($password, $this->attributes['password']);
    }
}
