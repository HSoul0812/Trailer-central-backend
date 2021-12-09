<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;

/**
 * Class CreateLeadStatusRequest
 * @package App\Http\Requests\CRM\Leads
 */
class CreateLeadStatusRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer|exists:App\Models\User\User,dealer_id',
        'tc_lead_identifier' => 'required|integer|valid_lead',
        'status' => 'required|string',
        'sales_person_id' => 'integer|exists:App\Models\CRM\User\SalesPerson,id',
    ];
}
