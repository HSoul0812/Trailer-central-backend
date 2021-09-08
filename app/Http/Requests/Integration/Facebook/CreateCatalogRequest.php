<?php

namespace App\Http\Requests\Integration\Facebook;

use App\Http\Requests\Request;
use App\Models\Integration\Facebook\Catalog;

/**
 * Create Catalog Request
 * 
 * @author David A Conway Jr.
 */
class CreateCatalogRequest extends Request {
    protected function getRules(): array
    {
        return array_merge([
            'dealer_location_id' => 'required|integer',
            'business_id' => 'integer',
            'catalog_id' => 'required|integer',
            'catalog_name' => 'required|string|max:255',
            'account_name' => 'required|string',
            'account_id' => 'required|integer',
            'page_title' => 'required|string',
            'page_id' => 'required|integer',
            'page_token' => 'nullable|string|max:255',
            'access_token' => 'required|string|max:255',
            'id_token' => 'string',
            'issued_at' => 'date_format:Y-m-d H:i:s',
            'expires_at' => 'date_format:Y-m-d H:i:s',
            'expires_in' => 'integer',
            'scopes' => 'required|array',
            'scopes.*' => 'required|string|max:80',
            'is_active' => 'nullable|boolean',
            'filters' => 'nullable|json'
        ], [
            'catalog_type' => 'required|in:' . implode(",", Catalog::CATALOG_TYPES)
        ]);
    }
}