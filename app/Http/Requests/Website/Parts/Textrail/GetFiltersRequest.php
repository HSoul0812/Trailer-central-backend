<?php

namespace App\Http\Requests\Website\Parts\Textrail;

use App\Http\Requests\Request;

/**
 *
 *
 * @author Eczek
 */
class GetFiltersRequest extends Request {

    protected $rules = [
        'type_id' => 'array',
        'manufacturer_id' => 'array',
        'brand_id' => 'array',
        'category_id' => 'array',
        'subcategory' => 'array',
    ];

}