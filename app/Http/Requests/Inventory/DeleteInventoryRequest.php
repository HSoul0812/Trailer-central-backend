<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;
use App\Models\CRM\Dms\UnitSale;

/**
 * Class DeleteInventoryRequest
 * @package App\Http\Requests\Inventory
 */
class DeleteInventoryRequest extends Request
{
    protected $rules = [
        'id' => 'required|integer|inventory_valid'
    ];
    
    public function validate(): bool {
        $valid = parent::validate();
        if ($valid) {
            // Do not allow deleting inventory linked to quotes
            $unitSale = UnitSale::where('inventory_id', $this->id)->first();
            if ($unitSale) {
                $valid = false;
            }
        }
        return $valid;
    }
}
