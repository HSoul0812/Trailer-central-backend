<?php

namespace App\Models\WebsiteUser;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class WebsiteUser extends Model implements CanResetPasswordContract
{
    use HasFactory, Notifiable;
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

    public function getEmailForPasswordReset()
    {
        return $this->email;
    }


    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
