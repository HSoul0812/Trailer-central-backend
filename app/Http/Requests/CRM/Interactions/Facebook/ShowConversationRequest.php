<?php

namespace App\Http\Requests\CRM\Interactions\Facebook;

use App\Http\Requests\Request;

/**
 * Class ShowConversationRequest
 * 
 * @package App\Http\Requests\CRM\Interactions\Facebook
 */
class ShowConversationRequest extends Request
{
    protected $rules = [
        'id' => 'required_without_all:conversation_id|int',
        'conversation_id' => 'required_without_all:id|string'
    ];
}
