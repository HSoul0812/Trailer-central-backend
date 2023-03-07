<?php

namespace App\Models\User;

use App\Models\CRM\User\SalesPerson;
use App\Models\User\Interfaces\PermissionsInterface;
use App\Services\User\UserService;
use App\Traits\Models\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DealerUser extends Model implements Authenticatable, PermissionsInterface
{
    use HasPermissions;

    const TABLE_NAME = 'dealer_users';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'password',
        'dealer_id',
        'salt',
    ];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'dealer_user_id';

    public $timestamps = false;

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return $this->email;
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->dealer_user_id;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
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
     *
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
     * Get Access Token
     *
     * @return type
     */
    public function getAccessTokenAttribute()
    {
        $authToken = AuthToken::where('user_id', $this->dealer_user_id)
            ->where('user_type', 'dealer_user')->firstOrFail();

        return $authToken->access_token;
    }

    /**
     * Get dealer
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get new dealer user
     */
    public function newDealerUser()
    {
        return $this->hasOne(NewDealerUser::class, 'id', 'dealer_id');
    }

    public function authToken(): HasOne
    {
        return $this
            ->hasOne(AuthToken::class, 'user_id', 'dealer_user_id')
            ->where('user_type', 'dealer_user');
    }

    public function getWebsiteAttribute()
    {
        return $this->user->website;
    }

    /**
     * Get dealer user permissions
     */

    /**
     * Get sales person
     */
    public function getSalesPersonAttribute()
    {
        // Get Sales Person ID From Perms
        $salesPersonId = $this->perms()->where('feature', 'crm')->pluck('permission_level')->first();

        // Find Sales Person
        return SalesPerson::find($salesPersonId);
    }

    public function getCrmLoginUrl(string $route = '')
    {
        $userService = app(UserService::class);
        $crmLoginString = $userService->getUserCrmLoginUrl($this->getAuthIdentifier(), $this);
        if ($route) {
            $crmLoginString .= '&r=' . $route;
        }

        return $crmLoginString;
    }

    public function isSecondaryUser(): bool
    {
        return true;
    }

    public static function getTableName()
    {
        return self::TABLE_NAME;
    }

    public function getDealerId(): int
    {
        return $this->dealer_id;
    }

    /**
     * Get By Sales Person
     *
     * @param int $salesPersonId
     */
    public static function getBySalesPerson($salesPersonId)
    {
        // Get Dealer User By Sales Person
        return self::select(self::getTableName() . '.*')
            ->leftJoin(DealerUserPermission::getTableName(), DealerUserPermission::getTableName() . '.dealer_user_id', '=', self::getTableName() . '.dealer_user_id')
            ->where(DealerUserPermission::getTableName() . '.feature', 'crm')
            ->where(DealerUserPermission::getTableName() . '.permission_level', $salesPersonId)
            ->first();
    }

    /**
     * @return hasMany
     */
    public function perms(): hasMany
    {
        return $this->hasMany(DealerUserPermission::class, 'dealer_user_id', 'dealer_user_id');
    }

    /**
     * @param string|null $value
     */
    public function setEmailAttribute(?string $value = null)
    {
        $this->attributes['email'] = !empty($value) ? strtolower($value) : null;
    }

    /**
     * @return string|null
     */
    public function getEmailAttribute(): ?string
    {
        return !empty($this->attributes['email']) ? strtolower($this->attributes['email']) : null;
    }

    public function logo(): HasOne
    {
        return $this->hasOne(DealerLogo::class, 'dealer_id', 'dealer_id');
    }
}
