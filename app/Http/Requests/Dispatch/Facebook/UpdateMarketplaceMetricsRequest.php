<?php

namespace App\Http\Requests\Dispatch\Facebook;

use App\Http\Requests\Request;

/**
 * Update Facebook Marketplace Metrics Request
 */
class UpdateMarketplaceMetricsRequest extends Request
{
    protected $rules = [
        'id' => 'integer|exists:fbapp_marketplace,id',
        'category' => 'nullable|string',
        'name' => 'required|string',
        'value' => 'required',
    ];
}
