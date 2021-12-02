<?php

namespace App\Http\Requests\CRM\Interactions\Message;

use App\Http\Requests\Request;

/**
 * Class SearchCountOfUnreadRequest
 * @package App\Http\Requests\CRM\Interactions\Message
 */
class SearchCountOfRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'group_by' => 'string|required|in:message_type',
        'hidden' => 'boolean',
        'dispatched' => 'boolean',
        'is_read' => 'boolean',
        'unique_leads' => 'boolean',
    ];
}
