<?php

namespace App\Models\CRM\Text;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Text Campaign Sent
 *
 * @package App\Models\CRM\Text
 */
class CampaignSent extends Model
{
    protected $table = 'crm_text_campaign_sent';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_id',
        'text_id'
    ];
}