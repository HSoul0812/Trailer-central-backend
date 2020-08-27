<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

class GetInventoryRequest extends Request {
    
    protected $rules = [
        'per_page' => 'integer',
        'sort' => 'in:title,-title,vin,-vin,manufacturer,-manufacturer,fp_balance,-fp_balance,fp_interest_paid,-fp_interest_paid,true_cost,-true_cost,fp_committed,-fp_committed',
        'search_term' => 'string',
        'dealer_id' => 'array',
        'dealer_id.*' => 'integer',
        'dealer_id' => 'integer',
        'only_floorplanned' => 'boolean',
        'floorplan_vendor' => 'vendor_exists'
    ];
    
}
