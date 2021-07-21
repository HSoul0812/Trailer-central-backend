<?php

namespace App\Http\Requests\Dms;

use App\Http\Requests\Request;
use App\Models\CRM\User\Customer;

/**
 * Class DeleteCustomerRequest
 * @package App\Http\Requests\Dms
 */
class DeleteCustomerRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|required|exists:dealer,dealer_id',
        'id' => 'integer|required'
    ];

    protected function getObject()
    {
        return new Customer();
    }

    public function getObjectIdValue() {
        return $this->input('id');
    }

    protected function validateObjectBelongsToUser() : bool {
        return true;
    }
}
