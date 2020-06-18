<?php

namespace App\Models\CRM\Text;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Text Blast
 *
 * @package App\Models\CRM\Text
 */
class Blast extends Model
{
    protected $table = 'crm_text_blast';

    // Define Constants to Make it Easier to Autocomplete
    const STATUS_ACTIONS = [
        'inquired',
        'purchased'
    ];

    const STATUS_ARCHIVED = [
        '0',
        '-1',
        '1'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'template_id',
        'campaign_name',
        'campaign_subject',
        'from_email_address',
        'action',
        'location_id',
        'send_after_days',
        'unit_category',
        'include_archived',
        'is_delivered',
        'is_cancelled',
        'deleted',
    ];
}