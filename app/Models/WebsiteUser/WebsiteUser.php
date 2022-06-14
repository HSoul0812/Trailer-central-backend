<?php

namespace App\Models\WebsiteUser;

use App\Notifications\WebsiteUserPasswordReset;
use App\Notifications\WebsiteUserVerifyEmail;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class WebsiteUser extends Model implements CanResetPasswordContract, MustVerifyEmailContract
{
    use HasFactory, Notifiable, MustVerifyEmail;
    protected $fillable = [
        'first_name',
        'last_name',
        'address',
        'zipcode',
        'city',
        'state',
        'email',
        'phone_number',
        'mobile_number'
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
}
