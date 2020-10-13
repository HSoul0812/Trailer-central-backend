<?php

namespace App\Http\Requests\Integration\Auth;

use App\Http\Requests\Request;

/**
 * Validate Token Request
 * 
 * @author David A Conway Jr.
 */
class ValidateTokenRequest extends Request {

    protected $rules = [
        'token_type' => 'required|valid_token_type',
        'relation_type' => 'required|valid_relation_type',
        'relation_id' => 'required|integer',
        'access_token' => 'required|string|max:255',
        'id_token' => 'required|string'
    ];
}