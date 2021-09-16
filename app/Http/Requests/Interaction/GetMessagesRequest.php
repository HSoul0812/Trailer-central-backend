<?php

namespace App\Http\Requests\Interaction;

use App\Http\Requests\Request;

/**
 * Class GetIntegrationsRequest
 * @package App\Http\Requests\Integration
 */
class GetMessagesRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|required|exists:App\Models\User\User,dealer_id'
    ];
}
