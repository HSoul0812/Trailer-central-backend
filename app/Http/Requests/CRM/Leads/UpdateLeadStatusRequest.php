<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;

/**
 * Class UpdateLeadStatusRequest
 * @package App\Http\Requests\CRM\Leads
 */
class UpdateLeadStatusRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer|exists:App\Models\User\User,dealer_id',
        'id' => 'required|integer|exists:App\Models\CRM\Leads\LeadStatus,id',
        'tc_lead_identifier' => 'integer|valid_lead',
        'status' => 'string',
        'sales_person_id' => 'integer|exists:App\Models\CRM\User\SalesPerson,id',
    ];
}
