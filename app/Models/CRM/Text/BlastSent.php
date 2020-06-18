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