<?php

namespace App\Http\Requests\CRM\Email;

use App\Http\Requests\Request;

/**
 * Get Blasts Request
 *
 * @author David A Conway Jr.
 */
class GetBlastsRequest extends Request
{
    protected $rules = [
        'campaign_name' => 'string',
        'per_page' => 'integer',
        'sort' => 'in:id,-id,name,-name',
        'id' => 'array',
        'id.*' => 'integer'
    ];
}
