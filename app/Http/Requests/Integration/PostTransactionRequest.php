<?php

namespace App\Http\Requests\Integration;

use App\Http\Requests\Request;

/**
 * Class PostTransactionRequest
 * @package App\Http\Requests\Integration
 */
class PostTransactionRequest extends Request
{
    protected $rules = [
        'integration_name' => 'required|string',
        'data' => 'required|string',
        'create_transaction_queue' => 'nullable|boolean'
    ];
}
