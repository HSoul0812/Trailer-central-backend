<?php

namespace App\Http\Requests\Feed\Factory;

use App\Http\Requests\Request;

/**
 * @author Marcel
 */
class GetShowroomsRequest extends Request {

    protected $rules = [
        'page' => 'integer',
        'search_term' => 'string|nullable',
        'manufacturer' => 'string|nullable',
        'model' => 'string|nullable',
        'select' => 'array',
        'select.*' => 'string|in:model,id',
        'with' => 'array',
        'with.*' => 'string|in:images,category,features',
    ];
}
