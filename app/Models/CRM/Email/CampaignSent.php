<?php

namespace App\Models\CRM\Email;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Email Campaign Sent
 *
 * @package App\Models\CRM\Email
 */
class CampaignSent extends Model
{
    protected $table = 'crm_drip_campaigns_sent';

    /**
     * Define Constants to Make it Easier to Handle Sent Types
     * 
     * @var array
     */
    const STATUS_TYPES = [
        'landline', // not a valid mobile number
        'invalid', // not a valid number
        'sent', // sent email
        'lead', // lead updated
        'logged' // logged email
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
        'email_campaign_id',
        'lead_id',
        'email_id',
        'status'
    ];
}