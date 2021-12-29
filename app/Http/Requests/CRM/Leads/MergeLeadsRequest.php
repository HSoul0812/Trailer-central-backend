<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;

/**
 * Class MergeLeadsRequest
 * @package App\Http\Requests\CRM\Leads
 */
class MergeLeadsRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer|exists:dealer,dealer_id',
        'lead_id' => 'required|integer|valid_lead',
        'merges_lead_id' => 'required|integer|valid_lead',
    ];
}
