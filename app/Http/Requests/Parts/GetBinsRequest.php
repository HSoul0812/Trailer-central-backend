<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 *
 * @author Marcel
 */
class GetBinsRequest extends Request
{

    protected $rules = [
        'dealer_id' => 'array',
        'dealer_id.*' => 'integer',
        'search_term' => 'string'
    ];

}
