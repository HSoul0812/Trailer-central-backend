<?php

namespace App\Models\Website;

use App\Models\Website\Config\WebsiteConfig;
use Illuminate\Database\Eloquent\Model;

class Website extends Model {

    protected $table = 'website';

    public function websiteConfigs()
    {
        return $this->hasMany(WebsiteConfig::class, 'website_id', 'id');
    }
}
