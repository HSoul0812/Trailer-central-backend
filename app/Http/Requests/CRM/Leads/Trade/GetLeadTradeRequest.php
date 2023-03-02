<?php

namespace App\Http\Requests\CRM\Leads\Trade;

use App\Http\Requests\Request;

/**
 * Class GetLeadTradeRequest
 * @package App\Http\Requests\CRM\Leads\Trade
 */
class GetLeadTradeRequest extends Request
{
    protected $rules = [
        'lead_id' => 'required|integer|valid_Lead',
        'id' => 'required|lead_trade_valid:lead_id'
    ];
}
