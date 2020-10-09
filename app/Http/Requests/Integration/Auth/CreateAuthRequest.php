<?php

namespace App\Http\Requests\Integration\Auth;

use App\Http\Requests\Request;

/**
 * Create Auth Request
 * 
 * @author David A Conway Jr.
 */
class CreateAuthRequest extends Request {

    protected $rules = [
        'token_type' => 'required|token_type_valid',
        'relation_type' => 'required|relation_type_valid',
        'relation_id' => 'required|relation_valid',
        'access_token' => 'required|string|max:255',
        'id_token' => 'string|max:255',
        'issued_at' => 'integer',
        'expires_at' => 'integer'
    ];
}