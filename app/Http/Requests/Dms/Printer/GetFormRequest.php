<?php

namespace App\Http\Requests\Dms\Printer;

use App\Http\Requests\Request;

class GetFormRequest extends Request
{
    protected $rules = [
        'per_page' => 'integer|min:1|max:2000',
        'sort' => 'in:name,-name,region,-region,department,-department,division,-division',
        'name' => 'string',
        'region' => 'string|min:2',
        'department' => 'string',
        'division' => 'string',
        'search_term' => 'string|min:2',
    ];

}