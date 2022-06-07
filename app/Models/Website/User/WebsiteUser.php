<?php

namespace App\Models\Website\User;

use App\Models\Website\Website;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class WebsiteUser extends Model implements Authenticatable
{
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'website_id',
        'last_login'
    ];

    protected $table = 'website_user';
    public function token() {
        return $this->hasOne(WebsiteUserToken::class, 'website_user_id', 'id');
    }

    public function website() {
        return $this->belongsTo(Website::class, 'website_id');
    }

    public function favoriteInventories() {
        return $this->hasMany(WebsiteUserFavoriteInventory::class, 'website_user_id');
    }

    public function setPasswordAttribute($value) {
        $this->attributes['password'] = \Hash::make($value);
    }

    public function checkPassword($password) {
        return \Hash::check($password, $this->attributes['password']);
    }

    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getRememberToken()
    {
        return '';
    }

    public function setRememberToken($value) {}

    public function getRememberTokenName()
    {
        return '';
    }
}
