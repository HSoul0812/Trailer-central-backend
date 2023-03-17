<?php

namespace App\Models\CRM\Leads;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    protected $table = 'website_lead_trades';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_id',
        'type',
        'make',
        'model',
        'year',
        'price',
        'length',
        'width',
        'notes'
    ];

    /**
     * @return BelongsTo
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id', 'identifier');
    }

    /**
     * @return HasMany
     */
    public function images(): HasMany
    {
        return $this->hasMany(LeadTradeImage::class, 'trade_id', 'id');
    }
}
