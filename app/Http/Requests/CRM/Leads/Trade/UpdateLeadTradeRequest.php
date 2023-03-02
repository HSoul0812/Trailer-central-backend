<?php

namespace App\Http\Requests\CRM\Leads\Trade;

use App\Http\Requests\Request;

/**
 * Class UpdateLeadTradeRequest
 * @package App\Http\Requests\CRM\Leads\Trade
 */
class UpdateLeadTradeRequest extends Request
{
    protected $rules = [
        'lead_id' => 'required|valid_lead',
        'id' => 'required|lead_trade_valid:lead_id',
        'type' => 'string',
        'make' => 'string',
        'model' => 'string',
        'year' => 'int',
        'price' => 'numeric',
        'length' => 'numeric',
        'width' => 'numeric',
        'notes' => 'string',
        'new_images' => 'array',
        'new_images.*' => 'max:10000|mimes:jpg,jpeg,png,bmp',
        'existing_images' => 'array|nullable',
        'existing_images.*.id' => 'integer|required'
    ];
}
