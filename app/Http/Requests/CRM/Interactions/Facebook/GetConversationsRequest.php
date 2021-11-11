<?php

namespace App\Http\Requests\CRM\Interactions\Facebook;

use App\Http\Requests\Request;

/**
 * Class GetConversationsRequest
 * 
 * @package App\Http\Requests\CRM\Interactions\Facebook
 */
class GetConversationsRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer',
        'lead_id' => 'required|integer',
        'page_id' => 'nullable|int',
        'user_id' => 'nullable|int',
        'per_page' => 'integer',
        'sort' => 'in:created_at,-created_at,updated_at,-updated_at'
    ];
}
