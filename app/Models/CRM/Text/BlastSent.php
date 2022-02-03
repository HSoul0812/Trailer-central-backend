<?php

namespace App\Models\CRM\Text;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Text Blast Sent
 *
 * @package App\Models\CRM\Text
 */
class BlastSent extends Model
{
    use TableAware;

    protected $table = 'crm_text_blast_sent';

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
     * Statuses That Mean Text Failed to Send
     * 
     * @var array
     */
    const STATUS_FAILED = [
        'landline', // not a valid mobile number
        'invalid' // not a valid number
    ];

    /**
     * Statuses That Mean Text Was Sent
     * 
     * @var array
     */
    const STATUS_SUCCESS = [
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
        'text_blast_id',
        'lead_id',
        'text_id',
        'status'
    ];
}