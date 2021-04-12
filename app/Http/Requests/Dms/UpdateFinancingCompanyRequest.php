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

    protected function getObject()
    {
        return new FinancingCompany();
    }

    protected function getObjectIdValue() {
        return $this->input('id');
    }

    protected function validateObjectBelongsToUser() : bool {
        return true;
    }

}
