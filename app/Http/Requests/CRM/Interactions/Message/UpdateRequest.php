<?php

namespace App\Http\Requests\CRM\Interactions\Message;

use App\Http\Requests\Request;

/**
 * Class UpdateInteractionMessageRequest
 * @package App\Http\Requests\CRM\Interactions
 */
class UpdateRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'id' => 'integer|required|interaction_message_valid',
        'hidden' => 'boolean',
        'is_read' => 'boolean',
    ];
}
