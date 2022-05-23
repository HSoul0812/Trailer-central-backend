<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;

/**
 * Class GetProductsRequest
 * @package App\Http\Requests\CRM\Leads
 */
class GetProductsRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer|exists:App\Models\User\User,dealer_id',
        'website_lead_id' => 'integer',
    ];
}
