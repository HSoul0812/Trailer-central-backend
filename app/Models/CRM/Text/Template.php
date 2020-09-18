<?php

namespace App\Models\CRM\Text;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Text Template
 *
 * @package App\Models\CRM\Text
 */
class Template extends Model
{
    protected $table = 'crm_text_template';

    // Constant to Handle Reply STOP
    const REPLY_STOP = "\n\nReply \"STOP\" if you do not want to receive texts and promos from \"{dealer_name}\"";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'template',
        'deleted',
    ];
}