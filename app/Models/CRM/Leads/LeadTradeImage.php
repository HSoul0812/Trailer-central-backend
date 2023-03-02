<?php

namespace App\Models\CRM\Leads;

use Illuminate\Database\Eloquent\Model;

/**
 * Class LeadTradeImage
 * @package App\Models\CRM\Leads
 *
 * @property int $id
 * @property int $trade_id
 * @property string $filename
 * @property string $path
 * @property \DateTimeInterface $created_at
 */
class LeadTradeImage extends Model
{
    protected $table = 'website_lead_trade_image';

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
        'trade_id',
        'filename',
        'path'
    ];
}
