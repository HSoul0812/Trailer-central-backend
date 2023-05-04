<?php

namespace App\Models\WebsiteUser;

use App\Notifications\WebsiteUserPasswordReset;
use App\Notifications\WebsiteUserVerifyEmail;
use Hash;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class WebsiteUser extends Model implements CanResetPasswordContract, MustVerifyEmailContract, JWTSubject, AuthenticatableContract
{
    use HasFactory;
    use Notifiable;
    use MustVerifyEmail;
    use Authenticatable;
    protected $fillable = [
        'first_name',
        'last_name',
        'address',
        'zipcode',
        'city',
        'state',
        'email',
        'phone_number',
        'mobile_number',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new WebsiteUserPasswordReset($token));
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new WebsiteUserVerifyEmail());
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function setPasswordAttribute(string $password)
    {
        $this->attributes['password'] = Hash::make($password);
    }
}
