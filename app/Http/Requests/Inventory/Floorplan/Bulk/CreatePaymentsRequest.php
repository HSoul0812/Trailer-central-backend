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

    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->rules['paymentUUID'] = 'required|uuid|payment_uuid_valid:'.$this->input('dealer_id');
    }

}
