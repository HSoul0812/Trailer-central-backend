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
        'facebook' => 'Facebook'
    ];
    const TOKEN_GOOGLE = 'google';
    const TOKEN_FB = 'facebook';

    // Define Relation Types
    const RELATION_TYPES = [
        'sales_person' => 'Sales Person',
        'fbapp_page' => 'Facebook Page'
    ];

    // Define Supported Token Types
    const RELATION_TOKENS = [
        'sales_person' => 'google',
        'fbapp_page' => 'facebook'
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
        'access_token',
        'id_token',
        'issued_at',
        'expires_at'
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
    public function scope()
    {
        return $this->scopes()->pluck('scope')->toArray();
    }
}
