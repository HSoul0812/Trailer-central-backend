<?php

namespace App\Models\Integration\Facebook;

use App\Models\User\User;
use App\Models\Website\Website;
use App\Models\Integration\Facebook\Catalog;
use App\Models\Integration\Auth\AccessToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Page
 * @package App\Models\Integration\Facebook
 */
class Page extends Model
{
    // Define Table Name Constant
    const TABLE_NAME = 'fbapp_pages';

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
        'page_id',
        'title',
        'timestamp'
    ];

    /**
     * Get User
     * 
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get Website
     * 
     * @return BelongsTo
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get Catalogs
     * 
     * @return HasMany
     */
    public function catalogs()
    {
        return $this->hasMany(Catalog::class, 'fbapp_page_id', 'id');
    }

    /**
     * Access Token
     * 
     * @return HasOne
     */
    public function accessToken()
    {
        return $this->hasOne(AccessToken::class, 'relation_id', 'id')
                    ->whereTokenType('facebook')
                    ->whereRelationType('fbapp_page');
    }
}
