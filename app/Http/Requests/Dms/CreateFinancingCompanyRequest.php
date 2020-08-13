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
        'email' => 'email',
        'drivers_license' => 'required',
        'home_phone' => 'required',
        'work_phone' => 'required',
        'cell_phone' => 'required',
        'address' => 'required',
        'city' => 'required',
        'region' => 'required',
        'postal_code' => 'required',
        'country' => '',
        'tax_exempt' => 'required',
        'account_number' => 'required',
        'fin' => 'required',
        'gender' => '',
        'dob' => '',
    ];

    protected $filters = [];
}
