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
        'hub\.mode' => 'string',
        'hub\.token' => 'string',
        'hub\.challenge' => 'string'
    ];
}
