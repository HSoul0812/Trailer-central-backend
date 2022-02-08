<?php

namespace App\Models\Website\User;

use Illuminate\Database\Eloquent\Model;

class WebsiteUserSearchResult extends Model
{
    //
    protected $fillable = [
        'website_user_id',
        'search_url'
    ];
    protected $table = 'website_user_search_result';

    public function websiteUser() {
        return $this->belongsTo(WebsiteUser::class, 'website_user_id');
    }
}
