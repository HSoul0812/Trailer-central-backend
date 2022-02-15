<?php

declare(strict_types=1);

namespace App\Models\Integration\Facebook;

use App\Models\Integration\Facebook\Catalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Catalog
 * @package App\Models\Integration\Facebook
 */
class Feed extends Model
{
    use SoftDeletes;

    // Define Table Name Constant
    const TABLE_NAME = 'fbapp_feeds';

    /**
     * Define Catalog URL Prefix
     */
    const CATALOG_URL_PREFIX = 'facebook/catalog';

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
        'business_id',
        'catalog_id',
        'feed_id',
        'feed_title',
        'feed_url',
        'is_active',
        'imported_at'
    ];

    /**
     * Get Catalogs
     * 
     * @return HasMany
     */
    public function catalogs(): HasMany
    {
        return $this->hasMany(Catalog::class, 'catalog_id', 'catalog_id');
    }
}
