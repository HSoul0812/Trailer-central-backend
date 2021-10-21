<?php

namespace App\Models\Website\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WebsiteUserToken extends Model
{
    protected $fillable = [
        'access_token'
    ];

    //
    protected $table = 'website_user_token';

    public function user() {
        return $this->belongsTo(WebsiteUser::class, 'website_user_id');
    }
}
