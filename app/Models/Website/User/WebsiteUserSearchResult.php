<?php

namespace App\Models\Website\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read  WebsiteUser $websiteUser
 * @property int $website_user_id
 * @property string $search_url
 * @property string $summary
 * @property string $created_at
 */
class WebsiteUserSearchResult extends Model
{
    protected $fillable = [
        'website_user_id',
        'search_url',
        'summary'
    ];
    protected $table = 'website_user_search_result';

    public function websiteUser(): BelongsTo {
        return $this->belongsTo(WebsiteUser::class, 'website_user_id');
    }
}
