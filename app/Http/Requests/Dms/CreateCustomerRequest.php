<?php


namespace App\Http\Requests\Dms;


use App\Http\Requests\Request;

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

    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->rules['display_name'] = 'required|string|customer_name_unique:'.$this->input('dealer_id');
    }
} 
