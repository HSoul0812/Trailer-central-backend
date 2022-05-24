<?php

namespace App\Http\Requests\Marketing\Craigslist;

use App\Http\Requests\Request;
use App\Models\Inventory\Inventory;

class GetInventoryRequest extends Request {

    protected $rules = [
        'dealer_id' => 'required|integer',
        'profile_id' => 'required|integer|valid_clapp_profile',
        'per_page' => 'integer',
        'sort' => 'in:title,-title,stock,-stock,manufacturer,-manufacturer,' .
                    'category,-category,price,-price,status,-status,queue_id,-queue_id,' .
                    'last_posted,-last_posted,next_scheduled,-next_scheduled,' .
                    'created_at,-created_at,updated_at,-updated_at',
        'search_term' => 'string',
        'images_greater_than' => 'integer',
        'images_less_than' => 'integer',
        'units_with_true_cost' => 'boolean',
        'dealer_location_id' => 'dealer_location_valid',
        'type' => 'nullable|in:scheduler,archives,poster'
    ];

    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->rules['condition'] = 'in:'.implode(',', array_keys(Inventory::CONDITION_MAPPING));
    }
}
