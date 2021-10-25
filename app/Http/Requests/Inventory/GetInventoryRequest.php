<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;
use App\Models\Inventory\Inventory;

class GetInventoryRequest extends Request {

    protected $rules = [
        'per_page' => 'integer',
        'sort' => 'in:title,-title,vin,-vin,manufacturer,-manufacturer,fp_balance,-fp_balance,fp_interest_paid,-fp_interest_paid,true_cost,-true_cost,fp_committed,-fp_committed,fp_vendor,-fp_vendor,status,-status,created_at,-created_at,updated_at,-updated_at',
        'search_term' => 'string',
        'dealer_id.*' => 'integer',
        'dealer_id' => 'integer',
        'only_floorplanned' => 'boolean',
        'floorplan_vendor' => 'vendor_exists',
        'images_greater_than' => 'integer',
        'images_less_than' => 'integer',
        'units_with_true_Cost' => 'boolean',
        'dealer_location_id' => 'dealer_location_valid',
        'status' => 'integer',
        'inventory_ids' => 'array',
        'inventory_ids.*' => 'integer',
        'attribute_ids' => 'array',
    ];

    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->rules['condition'] = 'in:'.implode(',', array_keys(Inventory::CONDITION_MAPPING));
    }
}
