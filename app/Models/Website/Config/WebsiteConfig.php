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
 * @property string $website_id
 * @property string $key
 * @property string $value
 */
class WebsiteConfig extends Model
{
    const USER_ACCOUNTS_KEY = 'general/user_accounts';
    const INVENTORY_PRINT_LOGO_KEY = 'inventory/print_logo';
    const DURATION_BEFORE_AUTO_ARCHIVING_KEY = 'inventory/duration_before_auto_archiving';
    const MOBILE_KEY_ENABLED = 'general/mobile/enabled';
    const ECOMMERCE_KEY_ENABLE = 'parts/ecommerce/enabled';
    const GENERAL_HEAD_SCRIPT_KEY = 'general/head_script';
    const GENERAL_BODY_SCRIPT_KEY = 'general/body_script';
    const CALL_TO_ACTION = 'call-to-action';
    const SHOWROOM_USE_SERIES = 'showroom/use_series';
    const LEADS_MERGE_ENABLED = 'leads/merge/enabled';
    const PAYMENT_CALCULATOR_DURATION_KEY = 'payment-calculator/duration';

    protected $table = 'website_config';

    public $timestamps = false;

    protected $fillable = [
        "website_id",
        "key",
        "value"
    ];

    /**
     * @return BelongsTo
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class, 'website_id', 'id');
    }
}
