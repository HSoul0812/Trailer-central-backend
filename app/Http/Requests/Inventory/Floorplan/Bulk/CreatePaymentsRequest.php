<?php

namespace App\Http\Requests\Inventory\Floorplan\Bulk;

use App\Http\Requests\Request;


class CreatePaymentsRequest extends Request {

    protected $rules = [
        'payments' => 'required',
        'payments.*.inventory_id' => 'integer|required',
        'payments.*.type' => 'string|required',
        'payments.*.account_id' => 'integer|required',
        'payments.*.amount' => 'numeric|required',
        'payments.*.payment_type' => 'string|required',
        'payments.*.check_number' => 'string|nullable',
    ];

}
