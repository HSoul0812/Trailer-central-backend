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
        'search_term' => 'string'
    ];

}
