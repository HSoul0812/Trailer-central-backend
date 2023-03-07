<?php

namespace App\Http\Requests\Website\PaymentCalculator;

/**
 * @property int $id
 */
class UpdateSettingsRequest extends CreateSettingsRequest {
    public function getRules(): array
    {
        return array_merge($this->rules, [
            'id' => 'integer|min:1|required|exists:website_payment_calculator_settings,id,website_id,' . $this->website_id
        ]);
    }
}
