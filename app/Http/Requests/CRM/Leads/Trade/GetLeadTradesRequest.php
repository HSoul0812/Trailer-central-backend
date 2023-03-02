<?php

namespace App\Http\Requests\CRM\Leads\Trade;

use App\Http\Requests\Request;

/**
 * Class GetLeadTradesRequest
 * @package App\Http\Requests\CRM\Leads\Trade
 */
class GetLeadTradesRequest extends Request
{
    protected $rules = [
        'lead_id' => 'required|valid_lead',
        'sort' => 'in:id,-id,type,-type,make,-make,model,-model,year,-year,created_at,-created_at',
    ];
}
