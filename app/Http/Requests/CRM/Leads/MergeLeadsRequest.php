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
        'merge_lead_ids' => 'required|array',
        'merge_lead_ids.*' => 'integer|valid_lead',
    ];
}
