<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 * Get Part Orders Request
 *
 * @author David A Conway Jr.
 */
class GetPartOrdersRequest extends Request {

    protected $rules = [
        'dealer_id' => 'required|integer',
        'website_id' => 'integer',
        'per_page' => 'integer',
        'sort' => 'in:status,-status,fulfillment,-fulfillment,email,-email,phone,-phone,shipto,-shipto,created_at,-created_at,updated_at,-updated_at',
        'status' => 'array',
        'status.*' => 'valid_part_order_status',
        'fulfillment' => 'array',
        'fulfillment.*' => 'valid_part_fulfillment',
        'id' => 'array',
        'id.*' => 'integer'
    ];

    public function all($keys = null) {
        $all = parent::all($keys);

        return $all;
    }
}
