<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class WebsiteEntityCss extends Model
{
    protected $fillable = [
        'name',
        'content',
        'sort_order'
    ];

    protected $table = 'website_entity_css';
}
