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
        'id' => 'required|lead_trade_valid'
    ];
}
