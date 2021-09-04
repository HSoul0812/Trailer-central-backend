<?php

namespace App\Http\Requests\Integration\Facebook;

use App\Http\Requests\Request;
use App\Models\Integration\Facebook\Catalog;

/**
 * Update Catalog Request
 * 
 * @author David A Conway Jr.
 */
class UpdateCatalogRequest extends Request {
    protected function getRules(): array
    {
        return array_merge([
            'id' => 'required|integer',
            'dealer_location_id' => 'integer',
            'business_id' => 'integer',
            'catalog_id' => 'integer',
            'account_name' => 'string',
            'account_id' => 'integer',
            'page_title' => 'string',
            'page_id' => 'integer',
            'page_token' => 'nullable|string|max:255',
            'page_refresh_token' => 'nullable|string|max:255',
            'access_token' => 'string|max:255',
            'refresh_token' => 'nullable|string|max:255',
            'id_token' => 'string',
            'issued_at' => 'date_format:Y-m-d H:i:s',
            'expires_at' => 'date_format:Y-m-d H:i:s',
            'expires_in' => 'integer',
            'scopes' => 'array',
            'scopes.*' => 'string|max:80',
            'is_active' => 'nullable|boolean',
            'filters' => 'nullable|json'
        ], [
            'catalog_type' => 'required|in:' . implode(",", Catalog::CATALOG_TYPES)
        ]);
    }
    
}