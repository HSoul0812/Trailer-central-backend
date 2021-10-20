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
        'object' => 'string',
        'entry' => 'array',
        'entry.*.id' => 'string',
        'entry.*.time' => 'integer',
        'entry.*.messaging' => 'array',
        'entry.*.messaging.*.sender.id' => 'string',
        'entry.*.messaging.*.recipient.id' => 'string',
        'entry.*.messaging.*.timestamp' => 'integer',
        'entry.*.messaging.*.message' => 'array',
        'entry.*.messaging.*.message.mid' => 'string',
        'entry.*.messaging.*.message.text' => 'string'
    ];
}
