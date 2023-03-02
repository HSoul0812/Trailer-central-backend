<?php

namespace App\Http\Requests\CRM\Leads\Trade;

use App\Http\Requests\Request;

/**
 * Class DeleteLeadTradeRequest
 * @package App\Http\Requests\CRM\Leads
 */
class DeleteLeadTradeRequest extends Request
{
    protected $rules = [
        'lead_id' => 'required|integer',
        'id' => 'required|lead_trade_valid:lead_id'
    ];
}
