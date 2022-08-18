<?php


namespace App\Http\Requests\Dms;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class CreateCustomerRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|required',
        'first_name' => 'string|nullable',
        'last_name' => 'string|nullable',
        'email' => 'email|nullable',
        'drivers_license' => 'string|nullable',
        'home_phone' => 'string|nullable',
        'work_phone' => 'string|nullable',
        'cell_phone' => 'string|nullable',
        'address' => 'string|nullable',
        'city' => 'string|nullable',
        'region' => 'string|nullable',
        'postal_code' => 'string|nullable',
        'country' => 'string|nullable',
        'website_lead_id' => 'integer|nullable',
        'tax_exempt' => 'integer|nullable',
        'is_financing_company' => 'integer|nullable',
        'account_number' => 'string|nullable',
        'qb_id' => 'integer|nullable',
        'gender' => 'string|nullable',
        'dob' => 'string|nullable',
        'deleted_at' => 'date|nullable',
        'is_wholesale' => 'integer',
        'default_discount_percent' => 'required|numeric',
        'middle_name' => 'string|nullable',
        'company_name' => 'string|nullable',
        'use_same_address' => 'integer',
        'shipping_address' => 'string|nullable',
        'shipping_city' => 'string|nullable',
        'shipping_region' => 'string|nullable',
        'shipping_postal_code' => 'string|nullable',
        'shipping_country' => 'string|nullable',
        'county' => 'string|nullable',
        'shipping_county' => 'string|nullable',
    ];

    protected function getRules(): array
    {
        $this->rules['display_name'] = [
            'required',
            'string',
            Rule::unique('dms_customer', 'display_name')
                ->where('dealer_id', $this->get('dealer_id'))
                ->whereNull('deleted_at'),
        ];

        return parent::getRules();
    }
}
