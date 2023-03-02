<?php

namespace App\Http\Requests\CRM\Leads\Trade;

use App\Http\Requests\Request;

/**
 * Class CreateLeadTradeRequest
 * @package App\Http\Requests\CRM\Leads\Trade
 */
class CreateLeadTradeRequest extends Request
{
    protected $rules = [
        'lead_id' => 'required|valid_lead',
        'type' => 'nullable|string',
        'make' => 'required|string',
        'model' => 'required|string',
        'year' => 'required|int',
        'price' => 'nullable|numeric',
        'length' => 'nullable|numeric',
        'width' => 'nullable|numeric',
        'notes' => 'required|string',
        'images' => 'array',
        'images.*' => 'file|max:10000|mimes:jpg,jpeg,png,bmp'
    ];
}
