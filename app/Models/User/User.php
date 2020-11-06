<?php

namespace App\Models\User;

use App\Models\User\Interfaces\PermissionsInterface;
use App\Traits\Models\HasPermissionsStub;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Leads\Lead;
use App\Models\Website\Website;

/**
 * Class User
 *
 * This User class is for API users
 *
 * @package App\Models\User
 */
class User extends Model implements Authenticatable, PermissionsInterface
{
    use HasPermissionsStub;

    const TABLE_NAME = 'dealer';

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

    /**
     * Get dealer users
     */
    public function dealerUsers()
    {
        return $this->hasMany(DealerUser::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get leads
     */
    public function leads()
    {
        return $this->hasMany(Lead::class, 'dealer_id', 'dealer_id')->where('is_spam', 0);
    }

    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
