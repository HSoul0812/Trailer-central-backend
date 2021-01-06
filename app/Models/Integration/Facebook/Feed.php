<?php

namespace App\Models\Integration\Facebook;

use App\Models\Integration\Facebook\Catalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'date_imported'
    ];

    /**
     * Get Catalogs
     * 
     * @return HasMany
     */
    public function catalogs()
    {
        return $this->hasMany(Catalog::class, 'catalog_id', 'catalog_id');
    }
}
