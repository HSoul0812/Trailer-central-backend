<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 * @author Marcel
 */
class GetCycleCountsRequest extends Request
{

    protected $rules = [
        'dealer_id' => 'array',
        'dealer_id.*' => 'integer',
        'bin_id' => 'array',
        'bin_id.*' => 'integer',
        'is_completed' => 'integer'
    ];

}
