<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

/**
 * Class ExistsInventoryRequest
 * @package App\Http\Requests\Inventory
 */
class ExistsInventoryRequest extends Request
{
    protected $rules = [
        'stock' => 'string',
        'dealer_id' => 'required|exists:App\Models\User\User,dealer_id',
        'inventory_id' => 'exists:App\Models\Inventory\Inventory,inventory_id',
        'delaer_location_id' => 'exists:App\Models\User\DealerLocation,dealer_location_id',
        'entity_type_id' => 'exists:App\Models\Inventory\EntityType,entity_type_id',
    ];
}
