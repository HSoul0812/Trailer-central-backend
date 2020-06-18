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
        'website_id',
        'post_content',
        'meta_keywords',
        'meta_description',
        'title',
        'url_path',
        'entity_config',
        'date_created',
        'date_modified',
        'date_published',
        'status',
        'deleted',
    ];
}