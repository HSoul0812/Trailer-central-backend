<?php

namespace App\Http\Requests\CRM\Interactions\Facebook;

use App\Http\Requests\Request;

/**
 * Class GetMessagesRequest
 * 
 * @package App\Http\Requests\CRM\Interactions\Facebook
 */
class GetMessagesRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer',
        'lead_id' => 'required|integer',
        'conversation_id' => 'nullable|string',
        'page_id' => 'nullable|int',
        'user_id' => 'nullable|int',
        'per_page' => 'integer',
        'sort' => 'in:created_at,-created_at,updated_at,-updated_at'
    ];
}
