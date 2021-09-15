<?php

namespace App\Models\Website;

use App\Models\Website\Config\WebsiteConfig;
use Illuminate\Database\Eloquent\Model;
use App\Models\Website\Blog\Post;
use App\Models\User\User;

/**
 * Class Website
 * @package App\Models\Website
 *
 * @property int $id
 * @property string $domain
 * @property string $canonical_host
 * @property string $render
 * @property bool $render_cms
 * @property bool $https_supported
 * @property string $type
 * @property string $template
 * @property bool $responsive
 * @property int $dealer_id
 * @property string $type_config
 * @property float $handling_fee
 * @property int $parts_fulfillment
 * @property \DateTimeInterface $date_created
 * @property int $date_updated
 * @property bool $is_active
 * @property bool $is_live
 * @property string $parts_email
 * @property bool $force_elastic
 */
class Website extends Model
{
    const WEBSITE_TYPE_CLASSIFIED = 'classified';

    protected $table = 'website';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'date_created';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'date_updated';

    public function dealer()
    {
        return $this->hasOne(User::class, 'dealer_id', 'dealer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function websiteConfigs()
    {
        return $this->hasMany(WebsiteConfig::class, 'website_id', 'id');
    }

    public function blogPosts()
    {
        return $this->hasMany(Post::class, 'website_id', 'id');
    }

    /**
     * @param string $key
     * @return array
     */
    public function websiteConfigByKey(string $key)
    {
        return $this->websiteConfigs()->where('key', $key)->take(1)->value('value');
    }
}
