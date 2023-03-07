<?php

namespace App\Models\Marketing\Facebook;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

class MarketplaceMetric extends Model
{
    use TableAware;

    const TABLE_NAME = 'fbapp_marketplace_metrics';
    protected $table = self::TABLE_NAME;
    protected $primaryKey = 'id';
    protected $fillable = [
        'marketplace_id',
        'category',
        'name',
        'value'
    ];

    public function marketplace()
    {
        return $this->belongsTo(Marketplace::class);
    }
}
