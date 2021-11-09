<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory\Overlay;

use App\Http\Requests\WithDealerRequest;
use App\Models\Inventory\CustomOverlay;
use Illuminate\Validation\Rule;

/**
 * @property string $name
 * @property string $value
 */
class UpdateCustomOverlayRequest extends WithDealerRequest
{
    public function getRules(): array
    {
        return array_merge($this->rules, [
            'name' => ['required', Rule::in(CustomOverlay::VALID_CUSTOM_NAMES)],
            'value' => 'required|string|max:255',
        ]);
    }
}
