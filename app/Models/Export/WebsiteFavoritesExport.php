<?php

namespace App\Models\Export;

use Illuminate\Database\Eloquent\Model;

class WebsiteFavoritesExport extends Model
{
    protected $fillable = [
        'website_id',
        'last_ran'
    ];

    public static function logRun($websiteId)
    {
        return self::updateOrCreate(
            ['website_id' => $websiteId],
            ['last_ran' => now()]
        );
    }
}
