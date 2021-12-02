<?php

namespace App\Http\Requests\CRM\Interactions\Facebook;

use App\Http\Requests\Request;

/**
 * Class MessageWebhookRequest
 * 
 * @package App\Http\Requests\CRM\Interactions\Facebook
 */
class MessageWebhookRequest extends Request
{
    protected $rules = [
        'object' => 'required|string',
        'entry' => 'required|array',
        'entry.*.id' => 'required|string',
        'entry.*.time' => 'required|integer',
        'entry.*.messaging' => 'required|array',
        'entry.*.messaging.*.sender.id' => 'required|string',
        'entry.*.messaging.*.recipient.id' => 'required|string',
        'entry.*.messaging.*.timestamp' => 'required|integer',
        'entry.*.messaging.*.message' => 'required|array',
        'entry.*.messaging.*.message.mid' => 'required|string',
        'entry.*.messaging.*.message.text' => 'required|string'
    ];
}
