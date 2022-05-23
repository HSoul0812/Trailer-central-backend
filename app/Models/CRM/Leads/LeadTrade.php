<?php

namespace App\Models\CRM\Leads;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class LeadTrade
 * @package App\Models\CRM\Leads
 *
 * @property int $id
 * @property int $lead_id
 * @property string|null $type
 * @property string $make
 * @property string $model
 * @property int $year
 * @property float|null $price
 * @property float|null $length
 * @property float|null $width
 * @property string $notes
 * @property \DateTimeInterface $created_at
 *
 * @property LeadTradeImage<Collection> $images
 */
class LeadTrade extends Model
{
    const UPDATED_AT = NULL;

    protected $table = 'website_lead_trades';

    /**
     * @return HasMany
     */
    public function images(): HasMany
    {
        return $this->hasMany(LeadTradeImage::class, 'trade_id', 'id');
    }
}
