<?php

namespace App\Models\Website;

use App\Models\Website\Config\WebsiteConfig;
use Illuminate\Database\Eloquent\Model;
use App\Models\Website\Blog\Post;

class Website extends Model
{
    const WEBSITE_TYPE_CLASSIFIED = 'classified';

    protected $table = 'website';

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
