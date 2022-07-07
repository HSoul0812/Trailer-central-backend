<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;

/**
 * Class GetLeadTradesRequest
 * @package App\Http\Requests\CRM\Leads
 */
class GetLeadTradesRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer|exists:App\Models\User\User,dealer_id',
        'lead_id' => 'required|integer|valid_lead',
    ];
}
