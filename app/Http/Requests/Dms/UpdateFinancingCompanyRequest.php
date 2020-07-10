<?php


namespace App\Http\Requests\Dms;


use App\Http\Requests\Request;
use App\Models\CRM\Dms\FinancingCompany;

class UpdateFinancingCompanyRequest extends Request
{
    protected $rules = [
        'id' => 'required|integer',
        'dealer_id' => 'required|integer',
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
        'gender' => '',
        'dob' => '',
    ];

    protected $filters = [];

    protected function getObject()
    {
        return new FinancingCompany();
    }

    protected function getObjectIdValue() {
        return $this->input('id');
    }

    protected function validateObjectBelongsToUser() {
        return true;
    }

}
