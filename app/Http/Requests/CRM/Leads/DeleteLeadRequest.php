<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;

/**
 * Class DeleteLeadRequest
 * @package App\Http\Requests\CRM\Leads
 */
class DeleteLeadRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer',
        'id' => 'required|integer|valid_lead'
    ];
}
