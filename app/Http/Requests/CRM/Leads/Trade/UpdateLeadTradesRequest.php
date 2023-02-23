<?php

namespace App\Http\Requests\CRM\Leads\Trade;

use App\Http\Requests\Request;

/**
 * Class UpdateLeadTradesRequest
 * @package App\Http\Requests\CRM\Leads\Trade
 */
class UpdateLeadTradesRequest extends Request
{
    protected $rules = [
        'id' => 'required|valid_lead_trade',
        'type' => 'string',
        'make' => 'string',
        'model' => 'string',
        'year' => 'int',
        'price' => 'numeric',
        'length' => 'numeric',
        'width' => 'numeric',
        'notes' => 'string'
    ];
}
