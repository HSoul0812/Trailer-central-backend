<?php

namespace App\Models;

use App\Models\WebsiteUser\WebsiteUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTracking extends Model
{
    use HasFactory;

    const EVENT_PAGE_VIEW = 'page_view';

    protected $fillable = [
        'visitor_id',
        'website_user_id',
        'event',
        'url',
        'meta',
    ];

    protected $casts = [
        'meta' => 'json',
    ];

    public function websiteUser(): BelongsTo
    {
        return $this->belongsTo(WebsiteUser::class);
    }
}
