<?php

namespace App\Http\Requests\Dms;

use App\Http\Requests\Request;

/**
 * @author Marcel
 */
class GetServiceOrdersRequest extends Request {

    protected $rules = [
        'dealer_id' => 'integer',
        'status' => 'string',
        'date_in_or_date_out_lte' => 'date_format:Y-m-d',
        'date_in_or_date_out_gte' => 'date_format:Y-m-d',
        'inventory_ids' => 'array',
        'inventory_ids.*' => 'integer',
    ];

}
