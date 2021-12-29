<?php

namespace App\Http\Requests\CRM\Interactions\Message;

use App\Http\Requests\Request;

/**
 * Class SearchInteractionLead
 * @package App\Http\Requests\CRM\Interactions
 */
class SearchRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'page' => 'integer|required',
        'per_page' => 'integer',
        'message_type' => 'string|in:sms,email,fb',
        'query' => 'string',
        'hidden' => 'boolean',
        'dispatched' => 'boolean',
        'is_read' => 'boolean',
        'latest_messages' => 'boolean',
        'unassigned' => 'boolean',
        'sort' => 'string|in:date_sent,-date_sent',
    ];
}
