<?php

namespace App\Http\Requests\Dispatch\Facebook;

use App\Http\Requests\Request;

/**
 * Step Facebook Marketplace Request Status
 * 
 * @package App\Http\Requests\Dispatch\Facebook
 * @author David A Conway Jr.
 */
class StepMarketplaceRequest extends Request {

    protected $rules = [
        'step' => 'required|string',
        'action' => 'required|in:choose,create,update,delete,error',
        'inventory_id' => 'nullable|inventory_valid',
        'logs' => 'nullable|json',
        'error' => 'nullable|string',
        'message' => 'nullable|string'
    ];

}