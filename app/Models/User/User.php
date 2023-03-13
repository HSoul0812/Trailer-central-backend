<?php

namespace App\Models\User;

use App\Models\Parts\Bin;
use App\Models\Parts\Part;
use Laravel\Cashier\Billable;
use App\Traits\CompactHelper;
use App\Models\CRM\Leads\Lead;
use App\Models\Website\Website;
use App\Models\CRM\Leads\LeadType;
use App\Services\User\UserService;
use App\Models\Inventory\Inventory;
use Illuminate\Database\Query\Builder;
use App\Models\Integration\Integration;
use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Dms\Printer\Settings;
use App\Traits\Models\HasPermissionsStub;
use App\Models\CRM\Dms\Quote\QuoteSetting;
use App\Models\Website\Config\WebsiteConfig;
use App\Models\Marketing\Facebook\Marketplace;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Models\Integration\Collector\Collector;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Services\Common\EncrypterServiceInterface;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User\Interfaces\PermissionsInterface;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class User
 *
 * This User class is for API users
 *
 * @package App\Models\User
 *
 * @property int $dealer_id
 * @property string $name
 * @property string $email
 *
 * @property bool $clsf_active;
 * @property bool $isCrmActive
 * @property bool $is_dms_active
 * @property bool $is_scheduler_active
 * @property bool|null $use_description_in_feed
 * @property string|null $default_description
 * @property string|null $import_config
 * @property string $identifier
 * @property integer $showroom
 * @property string $showroom_dealers a PHP serialized object
 * @property int $auto_import_hide
 *
 * @method static Builder whereIn($column, $values, $boolean = 'and', $not = false)
 */
class User extends Model implements Authenticatable, PermissionsInterface
{
    use HasPermissionsStub, Billable;

    /**
     * @var string
     */
    const TABLE_NAME = 'dealer';

    /**
     * @var string
     */
    public const TYPE_DEALER = 'dealer';

    /**
     * @var string
     */
    public const TYPE_MANUFACTURER = 'manufacturer';

    /**
     * @var string
     */
    public const TYPE_WEBSITE = 'website';

    /**
     * @var string
     */
    public const STATUS_SUSPENDED = 'suspended';

    /**
     * @var string
     */
    public const STATUS_ACTIVE = 'active';

    /**
     * @var string
     */
    public const STATUS_TRIAL = 'trial';

    /**
     * @var string
     */
    public const STATUS_EXTERNAL = 'external';

    /**
     * @var string
     */
    public const STATUS_SIGNUP = 'signup';

    /**
     * @var string
     */
    public const AUTO_IMPORT_MODEL_LAST_7 = 'model+last 7 of vin (default)';

    /**
     * @var string
     */
    public const AUTO_IMPORT_MODEL_VIN = 'model+vin';

    /**
     * @var string
     */
    public const AUTO_IMPORT_MODEL_LAST_4 = 'last 4 of vin';

    /**
     * @var int
     */
    public const AUTO_IMPORT_HIDE_NOT_HIDDEN = 0;

    /**
     * @var int
     */
    public const AUTO_IMPORT_HIDE_HIDDEN = 1;

    /**
     * @var int
     */
    public const AUTO_IMPORT_HIDE_ARCHIVED = 2;

    /**
     * @var int
     */
    public const USE_DESCRIPTION_IN_FEED = 1;

    /**
     * @var int
     */
    public const DONT_USE_DESCRIPTION_IN_FEED = 0;

    /**
     * @var int
     */
    public const USE_AUTO_MSRP = 1;

    /**
     * @var int
     */
    public const DONT_USE_AUTO_MSRP = 0;

    /**
     * @var string
     */
    public const OVERLAY_LOGO_POSITION_NONE = 'none';

    /**
     * @var string
     */
    public const OVERLAY_LOGO_POSITION_UPPER_LEFT = 'upper_left';

    /**
     * @var string
     */
    public const OVERLAY_LOGO_POSITION_UPPER_RIGHT = 'upper_right';

    /**
     * @var string
     */
    public const OVERLAY_LOGO_POSITION_LOWER_LEFT = 'lower_left';

    /**
     * @var string
     */
    public const OVERLAY_LOGO_POSITION_LOWER_RIGHT = 'lower_right';

    /**
     * @var string
     */
    public const OVERLAY_UPPER_NONE = 'none';

    /**
     * @var string
     */
    public const OVERLAY_UPPER_DEALER_NAME = 'dealer';

    /**
     * @var string
     */
    public const OVERLAY_UPPER_DEALER_PHONE = 'phone';

    /**
     * @var string
     */
    public const OVERLAY_UPPER_DEALER_LOCATION_NAME = 'location';

    /**
     * @var int
     */
    public const CLASSIFIED_ACTIVE = 1;

    /**
     * @var array
     */
    public const TYPES = [
        self::TYPE_DEALER,
        self::TYPE_MANUFACTURER,
        self::TYPE_WEBSITE
    ];

    /**
     * @var array
     */
    public const STATUSES = [
        self::STATUS_SUSPENDED,
        self::STATUS_ACTIVE,
        self::STATUS_TRIAL,
        self::STATUS_EXTERNAL,
        self::STATUS_SIGNUP
    ];

    /**
     * @var array
     */
    public const AUTO_IMPORT_SETTINGS = [
        self::AUTO_IMPORT_MODEL_LAST_7,
        self::AUTO_IMPORT_MODEL_VIN,
        self::AUTO_IMPORT_MODEL_LAST_4
    ];

    /**
     * @var array
     */
    public const AUTO_IMPORT_HIDE_SETTINGS = [
        self::AUTO_IMPORT_HIDE_NOT_HIDDEN => 'Auto-imported inventory is on website, not archived',
        self::AUTO_IMPORT_HIDE_HIDDEN => 'Auto-imported inventory is hidden from website',
        self::AUTO_IMPORT_HIDE_ARCHIVED => 'Auto-imported inventory is archived'
    ];

    /**
     * @var array
     */
    public const OVERLAY_LOGO_POSITIONS = [
        self::OVERLAY_LOGO_POSITION_NONE,
        self::OVERLAY_LOGO_POSITION_UPPER_LEFT,
        self::OVERLAY_LOGO_POSITION_UPPER_RIGHT,
        self::OVERLAY_LOGO_POSITION_LOWER_LEFT,
        self::OVERLAY_LOGO_POSITION_LOWER_RIGHT
    ];

    /**
     * @var array
     */
    public const OVERLAY_UPPER_SETTINGS = [
        self::OVERLAY_UPPER_NONE,
        self::OVERLAY_UPPER_DEALER_NAME,
        self::OVERLAY_UPPER_DEALER_PHONE,
        self::OVERLAY_UPPER_DEALER_LOCATION_NAME
    ];

    /**
     * @var array
     */
    public const OVERLAY_TEXT_SETTINGS = [
        self::OVERLAY_UPPER_DEALER_NAME,
        self::OVERLAY_UPPER_DEALER_PHONE,
        self::OVERLAY_UPPER_DEALER_LOCATION_NAME
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'dealer_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'name',
        'email',
        'password',
        'default_description',
        'use_description_in_feed',
        'auto_import_hide',
        'import_config',
        'auto_msrp',
        'auto_msrp_percent',
        'from',
        'clsf_active',
        'is_dms_active',
        'is_scheduler_active',
        'is_quote_manager_active',
        'google_feed_active'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'autoresponder_enable' => 'boolean',
        'is_dms_active' => 'boolean',
        'is_scheduler_active' => 'boolean',
        'clsf_active' => 'boolean',
        'is_quote_manager_active' => 'boolean',
        'google_feed_active' => 'boolean'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        self::created(function ($model) {
            AuthToken::create([
                'user_id' => $model->dealer_id,
                'user_type' => 'dealer',
                'access_token' => md5($model->dealer_id . uniqid())
            ]);
        });
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return $this->name;
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->dealer_id;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param string $value
     * @return void
     */
    public function setRememberToken($value)
    {
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
    }

    /**
     * Get dealer shorten identifier
     *
     * @return false|string
     */
    public function getIdentifierAttribute()
    {
        return CompactHelper::shorten($this->dealer_id);
    }

    /**
     * @return mixed
     */
    public function getAccessTokenAttribute()
    {
        $authToken = AuthToken::where('user_id', $this->dealer_id)->firstOrFail();
        return $authToken->access_token;
    }

    /**
     * @return mixed
     */
    public function website()
    {
        return $this->hasOne(Website::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get new dealer user
     */
    public function newDealerUser()
    {
        return $this->hasOne(NewDealerUser::class, 'id', 'dealer_id');
    }

    /**
     * @return HasOneThrough
     */
    public function crmUser(): HasOneThrough
    {
        return $this->hasOneThrough(CrmUser::class, NewDealerUser::class, 'id', 'user_id', 'dealer_id', 'user_id');
    }

    /**
     * @return HasOne
     */
    public function dealerParts(): HasOne
    {
        return $this->hasOne(DealerPart::class, 'dealer_id', 'dealer_id');
    }

    /**
     * @return HasMany
     */
    public function parts(): HasMany
    {
        return $this->hasMany(Part::class, 'dealer_id', 'dealer_id');
    }

    /**
     * @return HasOne
     */
    public function dealerClapp(): HasOne
    {
        return $this->hasOne(DealerClapp::class, 'dealer_id', 'dealer_id');
    }

    /**
     * @return HasMany
     */
    public function marketplaceIntegrations(): HasMany
    {
        return $this->hasMany(Marketplace::class, 'dealer_id', 'dealer_id');
    }

    /**
     * @return HasOne
     */
    public function authToken(): HasOne
    {
        return $this
            ->hasOne(AuthToken::class, 'user_id', 'dealer_id')
            ->where('user_type', 'dealer');
    }

    /**
     * @return HasOne
     */
    public function quoteSetting(): HasOne
    {
        return $this->hasOne(QuoteSetting::class, 'dealer_id', 'dealer_id');
    }

    /**
     * @return bool
     */
    public function getIsCdkActiveAttribute(): bool
    {
        return (bool)$this->getCdkAttribute();
    }

    /**
     * @return mixed
     */
    public function getCdkAttribute()
    {
        $cdk = $this->adminSettings()->where([
            ['setting', '=', 'website_leads_cdk_source_id']
        ])->first();

        if ($cdk) {
            return $cdk->setting_value;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function getIsCrmActiveAttribute(): bool
    {
        $crmUser = $this->crmUser()->first();
        return $crmUser instanceof CrmUser && $crmUser->active;
    }

    /**
     * @return bool
     */
    public function getIsPartsActiveAttribute(): bool
    {
        return !empty($this->dealerParts);
    }

    /**
     * @return bool
     */
    public function getIsMarketingActiveAttribute(): bool
    {
        return !empty($this->dealerClapp);
    }

    /**
     * @return bool
     */
    public function getIsFmeActiveAttribute(): bool
    {
        return $this->is_marketing_active && boolval(count($this->marketplaceIntegrations));
    }

    /**
     * @return bool
     */
    public function getIsMobileActiveAttribute(): bool
    {
        if (isset($this->website)) {
            return (bool)$this->website->websiteConfigByKey(WebsiteConfig::MOBILE_KEY_ENABLED);
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function getIsEcommerceActiveAttribute(): bool
    {
        if (isset($this->website)) {
            return (bool)$this->website->websiteConfigByKey(WebsiteConfig::ECOMMERCE_KEY_ENABLE);
        } else {
            return false;
        }
    }

    /**
     * @return bool|null
     */
    public function getIsUserAccountsActiveAttribute(): ?bool
    {
        if (isset($this->website)) {
            return (bool)$this->website->websiteConfigByKey(WebsiteConfig::USER_ACCOUNTS_KEY);
        } else {
            return false;
        }
    }

    /**
     * Get dealer users
     */
    public function dealerUsers()
    {
        return $this->hasMany(DealerUser::class, 'dealer_id', 'dealer_id');
    }

    /**
     * @return HasMany
     */
    public function locations(): HasMany
    {
        return $this->hasMany(DealerLocation::class, 'dealer_id', 'dealer_id');
    }

    /**
     * @return HasMany
     */
    public function adminSettings(): HasMany
    {
        return $this->hasMany(DealerAdminSetting::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get leads
     */
    public function leads()
    {
        return $this->hasMany(Lead::class, 'dealer_id', 'dealer_id')->where('is_spam', 0)
            ->where(Lead::getTableName() . '.lead_type', '<>', LeadType::TYPE_NONLEAD);
    }

    /**
     * Get Inventories
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get Integrations
     */
    public function integrations(): BelongsToMany
    {
        return $this->belongsToMany(Integration::class, 'integration_dealer', 'dealer_id', 'integration_id')->withPivot('active');
    }

    /**
     * Get Collector
     */
    public function collector()
    {
        return $this->hasOne(Collector::class, 'dealer_id', 'dealer_id');
    }

    /**
     * @return HasOne
     */
    public function printerSettings(): HasOne
    {
        return $this->hasOne(Settings::class, 'dealer_id', 'dealer_id');
    }

    /**
     * @return HasMany
     */
    public function bins(): HasMany
    {
        return $this->hasMany(Bin::class, 'dealer_id', 'dealer_id');
    }

    /**
     * @param string $route
     * @param bool $useNewDesign
     * @return string
     */
    public function getCrmLoginUrl(string $route = '', bool $useNewDesign = false): string
    {
        $userService = app(UserService::class);
        $crmLoginString = $userService->getUserCrmLoginUrl($this->getAuthIdentifier());
        if ($route) {
            $crmLoginString .= '&r=' . $route;
        }
        return ($useNewDesign ? config('app.new_design_crm_url') : '') . $crmLoginString;
    }

    /**
     * @return bool
     */
    public function isSecondaryUser(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * @return int
     */
    public function getDealerId(): int
    {
        return $this->dealer_id;
    }

    /**
     * Set the user's password encryption method
     *
     * @param string $value
     * @return void
     */
    public function setPasswordAttribute(string $value): void
    {
        $salt = $this->salt;
        $encrypterService = app(EncrypterServiceInterface::class);
        if (empty($salt)) {
            $salt = uniqid();
            $this->attributes['salt'] = $salt;
        }
        $this->attributes['password'] = $encrypterService->encryptBySalt($value, $salt);
    }

    /**
     * Unserializes and returns the serialized showroom dealers
     * @return array|null
     */
    public function getShowroomDealers(): ?array
    {
        if ($this->showroom_dealers) {
            return array_values(array_filter(unserialize($this->showroom_dealers)));
        }
        return null;
    }

    public function logo(): HasOne
    {
        return $this->hasOne(DealerLogo::class, 'dealer_id');
    }
}
