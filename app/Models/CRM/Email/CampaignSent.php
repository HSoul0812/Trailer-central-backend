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
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'date_added';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = NULL;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'drip_campaigns_id',
        'lead_id',
        'message_id'
    ];
}