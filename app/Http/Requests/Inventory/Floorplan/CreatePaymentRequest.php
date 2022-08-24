<?php

namespace App\Http\Requests\Inventory\Floorplan;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

/**
 *
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
        $this->rules['paymentUUID'] = 'required|uuid|payment_uuid_valid:' . $this->input('dealer_id');

        $this->rules['check_number'] = [
            'string',
            // 'nullable',
            Rule::unique('inventory_floor_plan_payment', 'check_number')
                ->where('dealer_id', request('dealer_id'))
                ->whereNull('deleted_at'),
        ];
    }
}
