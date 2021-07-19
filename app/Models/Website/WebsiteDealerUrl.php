<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

/**
 * Class WebsiteDealerUrl
 * @package App\Models\Website
 */
class WebsiteDealerUrl extends Model
{
    protected $table = 'website_dealer_url';

    protected $fillable = [
        'dealer_id',
        'location_id',
        'url',
    ];
}
