<?php

namespace App\Models\CRM\Text;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Text Blast Sent
 *
 * @package App\Models\CRM\Text
 */
class BlastSent extends Model
{
    protected $table = 'crm_text_blast_sent';

    // Define Constants to Make it Easier to Handle Sent Types
    const STATUS_TYPES = [
        'landline', // not a valid mobile number
        'invalid', // not a valid number
        'sent', // sent text
        'lead', // lead updated
        'logged' // logged text
    ];

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