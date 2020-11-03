<?php

namespace App\Models\Integration\Facebook;

use Illuminate\Database\Eloquent\Model;
use App\Models\Integration\Auth\AccessToken;

/**
 * Class Catalog
 * @package App\Models\Integration\Facebook
 */
class Catalog extends Model
{
    // Define Table Name Constant
    const TABLE_NAME = 'fbapp_catalog';

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
        'dealer_location_id',
        'account_name',
        'user_id',
        'filters'
    ];

    /**
     * Get the access token associated with the Catalog.
     */
    public function accessToken()
    {
        return $this->hasOne(AccessToken::class)->wherePivot('token_type', 'facebook')
                    ->wherePivot('relation_type', 'fbapp_catalog')
                    ->wherePivot('relation_id', $this->id);
    }
}
