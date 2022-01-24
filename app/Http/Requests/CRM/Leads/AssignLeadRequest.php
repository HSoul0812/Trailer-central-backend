<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;

/**
 * Class AssignLeadRequest
 * @package App\Http\Requests\CRM\Leads
 */
class AssignLeadRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer',
        'id' => 'required|integer|valid_lead|non_lead_exists',
        'lead_type' => 'required|string|lead_type_valid',
        'first_name' => 'string',
        'last_name' => 'string'
    ];
}
