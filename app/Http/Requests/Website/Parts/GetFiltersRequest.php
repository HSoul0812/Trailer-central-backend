<?php

namespace App\Http\Requests\Website\Parts;

use App\Http\Requests\Request;
use App\Rules\ValidTypeFilterRule;

/**
 *
 *
 * @author Eczek
 */
class GetFiltersRequest extends Request 
{
    /**
     * Get validation rules for a request
     */
    public function getRules()
    {
        return [
            'dealer_id' => 'required|array',
            'dealer_id.*' => 'integer',
            'type_id' => [
                'array',
                Rule::exists('part_types')->where(function ($query) {
                    return $query->whereIn('name', $this->type_id);
                }),
            ],
            'manufacturer_id' => 'array',
            'brand_id' => 'array',
            'category_id' => 'array',
            'subcategory' => 'array',
        ];
    }
}
