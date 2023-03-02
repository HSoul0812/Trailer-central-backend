<?php

namespace App\Models\Integration\Auth;

use App\Models\User\NewDealerUser;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AccessToken
 * @package App\Models\Integration\Auth
 */
class AccessToken extends Model
{
    // Define Table Name Constant
    const TABLE_NAME = 'integration_token';

    // Define Token Types
    const TOKEN_TYPES = [
        'google' => 'Google',
        'office365' => 'Office 365',
        'facebook' => 'Facebook'
    ];
    const TOKEN_GOOGLE = 'google';
    const TOKEN_OFFICE = 'office365';
    const TOKEN_FB = 'facebook';

    // Define Relation Types
    const RELATION_TYPES = [
        'system_emails' => 'System Email',
        'sales_person' => 'Sales Person',
        'fbapp_page' => 'Facebook Page',
        'fbapp_catalog' => 'Facebook Catalog',
        'fbapp_chat' => 'Facebook Chat',
        'clapp_accounts' => 'CL Email Account'
    ];

    // Define Supported Token Types
    const RELATION_TOKENS = [
        'system_emails' => 'google',
        'sales_person' => ['google', 'office365'],
        'fbapp_page' => 'facebook',
        'fbapp_catalog' => 'facebook',
        'fbapp_chat' => 'facebook',
        'clapp_accounts' => ['google', 'office365']
    ];

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'token_type',
        'relation_type',
        'relation_id',
        'state',
        'access_token',
        'refresh_token',
        'id_token',
        'expires_in',
        'expires_at',
        'issued_at',
        'scope'
    ];

    /**
     * Get new dealer user
     */
    public function newDealerUser()
    {
        return $this->belongsTo(NewDealerUser::class, 'id', 'dealer_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scopes()
    {
        return $this->hasMany(Scope::class, 'integration_token_id');
    }
    
    /**
     * @return array
     */
    public function getScopeAttribute()
    {
        return $this->scopes()->pluck('scope')->toArray();
    }

    /**
     * @param array $value
     */
    public function setScopeAttribute($value)
    {
        $this->attributes['scope'] = is_array($value) ? $value : explode(" ", $value);
    }

    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
