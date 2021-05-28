<?php

namespace App\Models\User;

use App\Models\User\Interfaces\PermissionsInterface;
use App\Traits\Models\HasPermissionsStub;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Leads\Lead;
use App\Models\Website\Website;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use App\Models\CRM\Dms\Printer\Settings;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use App\Models\User\DealerLocation;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
 *
 * @method static Builder whereIn($column, $values, $boolean = 'and', $not = false)
 */
class User extends Model implements Authenticatable, PermissionsInterface
{
    use HasPermissionsStub;

    const TABLE_NAME = 'dealer';

    public const TYPE_DEALER = 'dealer';

    public const TYPE_MANUFACTURER = 'manufacturer';

    public const TYPE_WEBSITE = 'website';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_TRIAL = 'trial';

    public const STATUS_EXTERNAL = 'external';

    public const STATUS_SIGNUP = 'signup';

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
        'password'
    ];

    protected $casts = [
        'autoresponder_enable' => 'boolean',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

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
        return $this->hasMany(Lead::class, 'dealer_id', 'dealer_id')->where('is_spam', 0);
    }

    public function printerSettings() : HasOne
    {
        return $this->hasOne(Settings::class, 'dealer_id', 'dealer_id');
    }

    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
