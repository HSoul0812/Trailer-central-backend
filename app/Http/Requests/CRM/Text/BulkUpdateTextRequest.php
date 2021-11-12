<?php

namespace App\Http\Requests\CRM\Text;

use App\Http\Requests\Request;

/**
 * Class BulkUpdateTextRequest
 * @package App\Http\Requests\CRM\Text
 */
class BulkUpdateTextRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer|exists:dealer,dealer_id',
        'ids' => 'array|required_without_all:search',
        'ids.*' => 'integer|required|valid_texts_log',
        'search' => 'array|required_without_all:ids',
        'search.lead_id' => 'integer|valid_lead',
        'lead_id' => 'integer|valid_lead'
    ];
}
