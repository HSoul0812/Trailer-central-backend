<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 *  
 * @author Eczek
 */
class GetVendorsRequest extends Request {
    
    protected $rules = [
        'dealer_id' => 'required',
        'name' => 'string',
        'show_on_part' => 'integer',
        'show_on_inventory' => 'integer',
        'show_on_floorplan' => 'integer'
    ];
    
}
