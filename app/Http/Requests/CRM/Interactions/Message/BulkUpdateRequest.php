<?php

namespace App\Http\Requests\CRM\Interactions\Message;

use App\Http\Requests\Request;

/**
 * Class BulkUpdateInteractionMessageRequest
 * @package App\Http\Requests\CRM\Interactions
 */
class BulkUpdateRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',

        'ids' => 'array|required_without_all:search_params',
        'ids.*' => 'integer|required|interaction_message_valid',

        'search_params' => 'array|required_without_all:ids',
        'search_params.lead_id' => 'integer|min:1|required_without_all:search_params.lead_ids',
        'search_params.lead_ids' => 'array|required_without_all:search_params.lead_id',
        'search_params.lead_ids.*' => 'integer|required',
        'search_params.is_read' => 'boolean',
        'search_params.message_type' => 'string|in:sms,email,fb',
        'search_params.hidden' => 'boolean',
        'search_params.dispatched' => 'boolean',

        'hidden' => 'boolean',
        'is_read' => 'boolean',
    ];
}
