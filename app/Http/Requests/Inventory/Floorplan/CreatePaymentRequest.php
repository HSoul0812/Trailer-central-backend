<?php

namespace App\Http\Requests\Inventory\Floorplan;

use App\Http\Requests\Request;
use App\Rules\Inventory\Floorplan\UniqueCheckNumberPaymentRule;

/**
 * @author Marcel
 */
class CreatePaymentRequest extends Request
{
    protected $rules = [
        'vendor_id' => 'integer|required',
        'account_id' => 'integer|required',
        'total_amount' => 'numeric|required',
        'payments' => 'required',
        'payments.*.inventory_id' => 'integer|required',
        'payments.*.amount' => 'numeric|required',
        'payments.*.type' => 'string|required',
    ];

    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $dealerId = request('dealer_id');

        $this->rules['paymentUUID'] = 'required|uuid|payment_uuid_valid:' . $dealerId;
        $this->rules['check_number'] = [
            'required',
            'string',
            new UniqueCheckNumberPaymentRule($dealerId),
        ];
    }
}
