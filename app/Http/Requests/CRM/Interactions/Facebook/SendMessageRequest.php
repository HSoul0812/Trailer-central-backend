<?php

namespace App\Http\Requests\CRM\Interactions\Facebook;

use App\Http\Requests\Request;

/**
 * Class MessageWebhookRequest
 * 
 * @package App\Http\Requests\CRM\Interactions\Facebook
 */
class SendMessageRequest extends Request
{
    protected $rules = [
        'lead_id' => 'required|integer',
        'message' => 'required|string',
        'type' => 'nullable|messaging_type_valid'
    ];
}
