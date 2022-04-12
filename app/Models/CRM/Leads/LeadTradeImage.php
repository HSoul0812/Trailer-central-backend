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
    const UPDATED_AT = NULL;

    protected $table = 'website_lead_trade_image';
}
