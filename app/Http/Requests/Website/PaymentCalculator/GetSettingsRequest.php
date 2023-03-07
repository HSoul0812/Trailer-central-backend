<?php

namespace App\Http\Requests\Website\PaymentCalculator;

use App\Http\Requests\WithDealerRequest;

/**
 * @property boolean $grouped
 */
class GetSettingsRequest extends WithDealerRequest {

    protected function getRules(): array
    {
        return array_merge(parent::getRules(), [
            'website_id' => 'integer|min:1|required|exists:website,id,dealer_id,' . $this->dealer_id,
            'inventory_condition' => 'in:used,new',
            'inventory_price' => 'numeric',
            'entity_type_id' => 'integer',
            'financing' => 'in:financing,no_financing',
            'grouped' => 'boolean'
        ]);
    }
}
