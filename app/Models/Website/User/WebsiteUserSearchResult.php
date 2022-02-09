<?php

namespace App\Models\Website\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read  WebsiteUser $websiteUser
 * @property $website_user_id
 * @property $search_url
 */
class WebsiteUserSearchResult extends Model
{
    protected $fillable = [
        'website_user_id',
        'search_url'
    ];
    protected $table = 'website_user_search_result';

    public function websiteUser(): BelongsTo {
        return $this->belongsTo(WebsiteUser::class, 'website_user_id');
    }
}
