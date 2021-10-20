<?php

namespace App\Http\Requests\CRM\Interactions\Facebook;

use App\Http\Requests\Request;

/**
 * Class MessageWebhookVerify
 * 
 * @package App\Http\Requests\CRM\Interactions\Facebook
 */
class MessageWebhookVerify extends Request
{
    protected $rules = [
        'hub.mode' => 'required|string',
        'hub.token' => 'required|string',
        'hub.challenge' => 'required|string'
    ];
}
