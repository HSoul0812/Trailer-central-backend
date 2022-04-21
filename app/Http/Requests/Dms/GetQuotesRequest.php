<?php

namespace App\Http\Requests\Dms;

use App\Http\Requests\Request;

/**
 * @author Marcel
 */
class GetQuotesRequest extends Request {

    protected $rules = [
        'dealer_id' => 'integer',
        'lead_id' => 'integer',
        'status' => 'string',
        'include_group_data' => 'boolean'
    ];

}
