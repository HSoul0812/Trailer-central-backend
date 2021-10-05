<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DealerWebsiteUserToken extends Model
{
    //
    protected $table = 'dealer_website_user_token';

    public function user() {
        return $this->belongsTo(DealerWebsiteUser::class, 'dealer_website_user_id');
    }
}
