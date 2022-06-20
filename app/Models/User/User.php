<?php

namespace App\Models\User;

use App\Models\User\Interfaces\PermissionsInterface;
use App\Traits\Models\HasPermissionsStub;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadType;
use App\Models\Website\Website;
use App\Models\Website\Config\WebsiteConfig;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use App\Models\CRM\Dms\Printer\Settings;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\User\UserService;
use App\Traits\CompactHelper;
use App\Services\Common\EncrypterServiceInterface;
use App\Models\User\AuthToken;
use Laravel\Cashier\Billable;

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
 * @property bool $isCrmActive
 * @property bool $is_dms_active
 * @property string $identifier
 *
 * @method static Builder whereIn($column, $values, $boolean = 'and', $not = false)
 */
class User extends Model implements Authenticatable, PermissionsInterface
{
    use HasPermissionsStub, Billable;

    const TABLE_NAME = 'dealer';

    public const TYPE_DEALER = 'dealer';

    public const TYPE_MANUFACTURER = 'manufacturer';

    public const TYPE_WEBSITE = 'website';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_TRIAL = 'trial';

    public const STATUS_EXTERNAL = 'external';

    public const STATUS_SIGNUP = 'signup';

    public const AUTO_IMPORT_MODEL_LAST_7 = 'model+last 7 of vin (default)';

    public const AUTO_IMPORT_MODEL_VIN = 'model+vin';

    public const AUTO_IMPORT_MODEL_LAST_4 = 'last 4 of vin';

    public const AUTO_IMPORT_HIDE_NOT_HIDDEN = 0;

    public const AUTO_IMPORT_HIDE_HIDDEN = 1;

    public const AUTO_IMPORT_HIDE_ARCHIVED = 2;

    public const USE_DESCRIPTION_IN_FEED = 1;

    public const DONT_USE_DESCRIPTION_IN_FEED = 0;

    public const USE_AUTO_MSRP = 1;

    public const DONT_USE_AUTO_MSRP = 0;

    public const OVERLAY_LOGO_POSITION_NONE = 'none';

    public const OVERLAY_LOGO_POSITION_UPPER_LEFT = 'upper_left';

    public const OVERLAY_LOGO_POSITION_UPPER_RIGHT = 'upper_right';

    public const OVERLAY_LOGO_POSITION_LOWER_LEFT = 'lower_left';

    public const OVERLAY_LOGO_POSITION_LOWER_RIGHT = 'lower_right';

    public const OVERLAY_UPPER_NONE = 'none';

    public const OVERLAY_UPPER_DEALER_NAME = 'dealer';

    public const OVERLAY_UPPER_DEALER_PHONE = 'phone';

    public const OVERLAY_UPPER_DEALER_LOCATION_NAME = 'location';

    public const TYPES = [
        self::TYPE_DEALER,
        self::TYPE_MANUFACTURER,
        self::TYPE_WEBSITE
    ];

    public const STATUSES = [
        self::STATUS_SUSPENDED,
        self::STATUS_ACTIVE,
        self::STATUS_TRIAL,
        self::STATUS_EXTERNAL,
        self::STATUS_SIGNUP
    ];

    public const AUTO_IMPORT_SETTINGS = [
        self::AUTO_IMPORT_MODEL_LAST_7,
        self::AUTO_IMPORT_MODEL_VIN,
        self::AUTO_IMPORT_MODEL_LAST_4
    ];

    public const AUTO_IMPORT_HIDE_SETTINGS = [
        self::AUTO_IMPORT_HIDE_NOT_HIDDEN => 'Auto-imported inventory is on website, not archived',
        self::AUTO_IMPORT_HIDE_HIDDEN => 'Auto-imported inventory is hidden from website',
        self::AUTO_IMPORT_HIDE_ARCHIVED => 'Auto-imported inventory is archived'
    ];

    public const OVERLAY_LOGO_POSITIONS = [
        self::OVERLAY_LOGO_POSITION_NONE,
        self::OVERLAY_LOGO_POSITION_UPPER_LEFT,
        self::OVERLAY_LOGO_POSITION_UPPER_RIGHT,
        self::OVERLAY_LOGO_POSITION_LOWER_LEFT,
        self::OVERLAY_LOGO_POSITION_LOWER_RIGHT
    ];

    public const OVERLAY_UPPER_SETTINGS = [
        self::OVERLAY_UPPER_NONE,
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
        'auto_msrp_percent'
    ];

    protected $casts = [
        'autoresponder_enable' => 'boolean',
        'is_dms_active' => 'boolean',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];
    
    public static function boot()
    {
        parent::boot();

        self::created(function($model){
            AuthToken::create([
                'user_id' => $model->dealer_id,
                'user_type' => 'dealer',
                'access_token' => md5($model->dealer_id.uniqid())
            ]);
        });
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName() {
        return $this->name;
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier() {
        return $this->dealer_id;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword() {}

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken() {}

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value) {}

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName() {}

    public function getIdentifierAttribute()
    {
        return CompactHelper::shorten($this->dealer_id);
    }

    public function getAccessTokenAttribute()
    {
        $authToken = AuthToken::where('user_id', $this->dealer_id)->firstOrFail();
        return $authToken->access_token;
    }

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

    public function crmUser(): HasOneThrough
    {
        return $this->hasOneThrough(CrmUser::class, NewDealerUser::class, 'id', 'user_id', 'dealer_id', 'user_id');
    }

    public function getIsCrmActiveAttribute(): bool
    {
        $crmUser = $this->crmUser()->first();
        return $crmUser instanceof CrmUser ? (bool)$crmUser->active : false;
    }

    public function getIsEcommerceActiveAttribute(): bool
    {
        $website = $this->website;

        if ($website) {
          $isWebsiteConfigEcommerce = WebsiteConfig::where('website_id', $website->id)->where('key', WebsiteConfig::ECOMMERCE_KEY_ENABLE)->where('value', 1)->exists();
        } else {
          $isWebsiteConfigEcommerce = false;
        }
        return $isWebsiteConfigEcommerce;
    }

    public function getIsUserAccountsActiveAttribute(): ?bool
    {
        if(isset($this->website)) {
            return $this->website->websiteConfigByKey('general/user_accounts');
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

    public function locations() : HasMany
    {
        return $this->hasMany(DealerLocation::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get leads
     */
    public function leads()
    {
        return $this->hasMany(Lead::class, 'dealer_id', 'dealer_id')->where('is_spam', 0)
                    ->where(Lead::getTableName() . '.lead_type', '<>', LeadType::TYPE_NONLEAD);
    }

    public function printerSettings() : HasOne
    {
        return $this->hasOne(Settings::class, 'dealer_id', 'dealer_id');
    }

    public function getCrmLoginUrl(string $route = '')
    {
        $userService = app(UserService::class);
        $crmLoginString = $userService->getUserCrmLoginUrl($this->getAuthIdentifier());
        if ($route) {
            $crmLoginString .= '&r='.$route;
        }
        return $crmLoginString;
    }

    public function isSecondaryUser() : bool
    {
        return false;
    }

    public static function getTableName() {
        return self::TABLE_NAME;
    }

    public function getDealerId(): int
    {
        return $this->dealer_id;
    }
    
    /**
     * Set the user's password encryption method
     *
     * @param  string  $value
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
}
