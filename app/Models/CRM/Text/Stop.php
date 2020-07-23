<?php

namespace App\Models\CRM\Text;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Text Stop
 *
 * @package App\Models\CRM\Text
 */
class Stop extends Model
{
    protected $table = 'crm_text_stop';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_id',
        'text_id',
        'response_id',
        'text_number'
    ];
}