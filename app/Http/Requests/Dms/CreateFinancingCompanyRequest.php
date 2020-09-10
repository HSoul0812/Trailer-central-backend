<?php


namespace App\Http\Requests\Dms;


use App\Http\Requests\Request;

class CreateFinancingCompanyRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required',
        'first_name' => 'required',
        'last_name' => 'required',
        'display_name' => 'required',
        'email' => 'nullable|email',
        'drivers_license' => 'nullable|string',
        'home_phone' => 'nullable|string',
        'work_phone' => 'nullable|string',
        'cell_phone' => 'nullable|string',
        'address' => 'nullable|string',
        'city' => 'nullable|string',
        'region' => 'nullable|string',
        'postal_code' => 'nullable|string',
        'country' => '',
        'tax_exempt' => 'nullable|string',
        'account_number' => 'nullable|string',
        'fin' => 'nullable|string',
        'gender' => '',
        'dob' => '',
    ];

    protected $filters = [];
} 
