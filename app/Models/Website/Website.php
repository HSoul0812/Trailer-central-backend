<?php

namespace App\Models\Website;

use App\Models\Website\Config\WebsiteConfig;
use Illuminate\Database\Eloquent\Model;
use App\Models\Website\Blog\Post;
use App\Models\User\User;

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
