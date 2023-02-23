<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;

/**
 * Class DeleteLeadTradeRequest
 * @package App\Http\Requests\CRM\Leads
 */
class DeleteLeadTradeRequest extends Request
{
    protected $rules = [
        'id' => 'required|valid_lead_trade'
    ];
}
