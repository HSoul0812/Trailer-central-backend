<?php

namespace App\Models\WebsiteUser;

use App\Support\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteUserCache extends Model
{
    use TableAware;
    protected $fillable = [
        'profile_data',
        'inventory_data',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(WebsiteUser::class);
    }
}
