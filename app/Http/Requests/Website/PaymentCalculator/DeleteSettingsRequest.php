<?php

namespace App\Http\Requests\Website\PaymentCalculator;

use App\Http\Requests\WithDealerRequest;
use Illuminate\Validation\Rule;

/**
 * @property int $id
 * @property int $website_id
 */
class DeleteSettingsRequest extends WithDealerRequest
{
    public function getRules(): array
    {
        return array_merge($this->rules, [
            'website_id' => [
                'required',
                Rule::exists('website', 'id')
                    ->where('dealer_id', $this->dealer_id)
            ],
            'id' => [
                'required',
                Rule::exists('website_payment_calculator_settings', 'id')
                    ->where('website_id', $this->website_id)
            ],
        ]);
    }
}
