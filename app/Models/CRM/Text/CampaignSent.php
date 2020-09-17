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
     * Define Constants to Make it Easier to Handle Sent Types
     * 
     * @var array
     */
    const STATUS_TYPES = [
        'landline', // not a valid mobile number
        'invalid', // not a valid number
        'sent', // sent text
        'lead', // lead updated
        'logged' // logged text
    ];

    /**
     * Define Constants for Specific Sent Types
     * 
     * @var array
     */
    const STATUS_LANDLINE = 'landline';
    const STATUS_INVALID = 'invalid';
    const STATUS_SENT = 'sent';
    const STATUS_LEAD = 'lead';
    const STATUS_LOGGED = 'logged';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'text_campaign_id',
        'lead_id',
        'text_id',
        'status'
    ];
}