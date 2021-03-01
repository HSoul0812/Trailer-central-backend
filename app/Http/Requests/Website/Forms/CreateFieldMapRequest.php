<?php

namespace App\Http\Requests\Website\Forms;

use App\Http\Requests\Request;

/**
 * Create Field Map Request
 * 
 * @author David A Conway Jr.
 */
class CreateFieldMapRequest extends Request {

    protected $rules = [
        'type'       => 'required|valid_form_map_type',
        'form_field' => 'required|string|max:50',
        'map_field'  => 'valid_form_map_field',
        'db_table'   => 'nullable|valid_form_map_table',
        'details'    => 'nullable|string'
    ];

}