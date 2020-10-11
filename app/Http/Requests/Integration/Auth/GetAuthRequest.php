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
        'token_type' => 'valid_token_type',
        'relation_type' => 'valid_relation_type',
        'relation_id' => 'valid_relation_id',
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
