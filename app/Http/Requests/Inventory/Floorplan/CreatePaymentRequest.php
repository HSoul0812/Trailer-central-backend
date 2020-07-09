<?php

namespace App\Http\Requests\Inventory\Floorplan;

use App\Http\Requests\Request;

/**
 *  
 * @author Marcel
 */
class CreatePaymentRequest extends Request {

    protected $rules = [
        'inventory_id' => 'integer|required',
        'type' => 'string|required',
        'account_id' => 'integer|required',
        'amount' => 'numeric|required',
        'payment_type' => 'string|required',
        'check_number' => 'string',
    ];

}
