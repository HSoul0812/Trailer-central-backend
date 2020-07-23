<?php

namespace App\Http\Requests\Feed\Factory;

use App\Http\Requests\Request;

/**
 * @author Marcel
 */
class GetShowroomRequest extends Request {

    protected $rules = [
        'page' => 'integer',
        'search_term' => 'string|nullable',
        'manufacturer' => 'string|nullable',
        'model' => 'string|nullable',
    ];
    
}