<?php

namespace App\Models\CRM\Leads\Jotform;

use Illuminate\Database\Eloquent\Model;

class WebsiteForms extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'website_forms';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'jotform_id',
        'website_id',
        'title',
        'username',
        'url',
        'status',
    ];
}
