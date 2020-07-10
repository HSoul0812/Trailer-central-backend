<?php


namespace App\Http\Requests\Dms;


use App\Http\Requests\Request;
use App\Models\CRM\Dms\FinancingCompany;

class DeleteFinancingCompanyRequest extends Request
{
    protected $rules = [
        'id' => 'required|integer'
    ];

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
