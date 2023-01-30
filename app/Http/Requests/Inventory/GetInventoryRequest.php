<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;
use App\Models\Inventory\Inventory;
use Illuminate\Validation\Rule;

/**
 * @property int $dealer_id
 * @property string $search_term
 */
class GetInventoryRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'per_page' => 'integer',
            'sort' => 'in:title,-title,vin,-vin,manufacturer,-manufacturer,fp_balance,-fp_balance,fp_interest_paid,-fp_interest_paid,true_cost,-true_cost,fp_committed,-fp_committed,fp_vendor,-fp_vendor,status,-status,created_at,-created_at,updated_at,-updated_at,stock,-stock,category,-category,price,-price,sales_price,-sales_price,archived_at,-archived_at',
            'search_term' => 'string',
            'dealer_id.*' => 'integer',
            'only_floorplanned' => 'boolean',
            'floorplan_vendor' => 'vendor_exists',
            'images_greater_than' => 'integer',
            'images_less_than' => 'integer',
            'units_with_true_cost' => 'boolean',
            'dealer_location_id' => 'dealer_location_valid',
            'status' => 'integer',
            'inventory_ids' => 'array',
            'inventory_ids.*' => 'integer',
            'attribute_names' => 'array',
            'model' => 'string',
            'exclude_status_ids' => 'array',
            'is_publishable_classified' => 'boolean',
            'exclude_status_ids.*' => [
                'int',
                Rule::in([
                    Inventory::STATUS_QUOTE,
                    Inventory::STATUS_AVAILABLE,
                    Inventory::STATUS_SOLD,
                    Inventory::STATUS_ON_ORDER,
                    Inventory::STATUS_PENDING_SALE,
                    Inventory::STATUS_SPECIAL_ORDER,
                ]),
            ],
            'condition' => Rule::in(array_keys(Inventory::CONDITION_MAPPING)),
        ];
    }
}
