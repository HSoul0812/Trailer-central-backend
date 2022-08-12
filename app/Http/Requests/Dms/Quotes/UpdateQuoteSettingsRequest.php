<?php

namespace App\Http\Requests\Dms\Quotes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuoteSettingsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'include_inventory_for_sales_tax' => 'boolean',
            'include_part_for_sales_tax' => 'boolean',
            'include_labor_for_sales_tax' => 'boolean',
            'include_fees_for_sales_tax' => 'boolean',
            'local_calculation_enabled' => 'boolean',
            'default_sales_location_id' => [
                'nullable',
                Rule::exists('dealer_location', 'dealer_location_id')
                    ->where('dealer_id', $this->input('dealer_id'))
            ],
        ];
    }
}
