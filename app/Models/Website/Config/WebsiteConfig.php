<?php

namespace App\Models\Website\Config;

use App\Models\Website\Website;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class WebsiteConfig
 * @package App\Models\Website\Config
 *
 * @property Website $website
 */
class WebsiteConfig extends Model
{
    const INVENTORY_PRINT_LOGO_KEY = 'inventory/print_logo';
    const DURATION_BEFORE_AUTO_ARCHIVING_KEY = 'inventory/duration_before_auto_archiving';

    protected $table = 'website_config';

    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class, 'website_id', 'id');
    }
}
