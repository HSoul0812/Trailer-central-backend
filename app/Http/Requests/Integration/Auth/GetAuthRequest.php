<?php

namespace App\Http\Requests\Integration\Auth;

use App\Http\Requests\Request;

/**
 * Get Auth Request
 * 
 * @author David A Conway Jr.
 */
class GetAuthRequest extends Request {
    
    protected $rules = [
        'token_type' => 'token_type_valid',
        'relation_type' => 'relation_type_valid',
        'relation_id' => 'relation_valid',
        'access_token' => 'string|max:255',
        'id_token' => 'string|max:255',
        'per_page' => 'integer',
        'sort' => 'in:issued_at,-issued_at,expires_at,-expires_at,created_at,-created_at,updated_at,-updated_at',
        'id' => 'array',
        'id.*' => 'integer'
    ];
    
    public function all($keys = null) {
        // Return Result
        $all = parent::all($keys);
        return $all;
    }
}
