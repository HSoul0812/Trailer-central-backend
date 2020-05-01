<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

/**
 *  
 * @author Marcel
 */
class GetFloorplanPaymentRequest extends Request {

    protected $rules = [
        'per_page' => 'integer',
        'sort' => 'in:type,-type,amount,-amount,payment_type,-payment_type,created_at,-created_at',
        'dealer_id' => 'integer|required',
    ];

}
