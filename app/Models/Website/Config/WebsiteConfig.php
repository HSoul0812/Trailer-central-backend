<?php

namespace App\Models\Website\Config;

use App\Models\Website\Website;
use Illuminate\Database\Eloquent\Model;

/**
 * Class WebsiteConfig
 * @package App\Models\Website\Config
 */
class WebsiteConfig extends Model
{
    const INVENTORY_PRINT_LOGO_KEY = 'inventory/print_logo'; 
    
    protected $table = 'website_config';
    
    public $timestamps = false;

    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id', 'id');
    }
}
