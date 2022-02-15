<?php


namespace App\Http\Requests\Dms;


use App\Http\Requests\Request;
use App\Models\CRM\Dms\FinancingCompany;

class DeleteFinancingCompanyRequest extends Request
{
    protected $rules = [
        'id' => 'required|integer'
    ];

    /**
     * Used by Request::validate() `this->getObject()`
     */
    protected function getObject()
    {
        return new FinancingCompany();
    }

    /**
     * Used by Request::validate() `$this->getObject()`
     */
    protected function getObjectIdValue()
    {
        return $this->input('id');
    }

    /**
     * Tells if request should check if object belongs to requesting user
     * Used by Request::validate() `$this->getObject()`
     */
    protected function validateObjectBelongsToUser(): bool
    {
        return true;
    }

}
