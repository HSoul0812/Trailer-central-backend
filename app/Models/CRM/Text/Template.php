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