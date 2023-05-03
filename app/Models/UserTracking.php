<?php

namespace App\Models;

use App\Models\WebsiteUser\WebsiteUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTracking extends Model
{
    use HasFactory;

    /**
     * We'll ignore location processing of these ip addresses.
     */
    public const IGNORE_LOCATION_PROCESSING_IP_ADDRESSES = [
        '127.0.0.1',
        '0.0.0.0',
    ];

    protected $fillable = [
        'visitor_id',
        'website_user_id',
        'event',
        'url',
        'page_name',
        'meta',
        'ip_address',
        'location_processed',
        'city',
        'state',
        'country',
    ];

    protected $casts = [
        'meta' => 'json',
        'location_processed' => 'boolean',
    ];

    public function websiteUser(): BelongsTo
    {
        return $this->belongsTo(WebsiteUser::class);
    }
}
