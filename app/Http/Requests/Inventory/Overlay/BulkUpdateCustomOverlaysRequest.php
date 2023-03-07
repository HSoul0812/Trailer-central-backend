<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory\Overlay;

use App\Http\Requests\WithDealerRequest;
use App\Models\Inventory\CustomOverlay;
use Illuminate\Validation\Rule;

/**
 * @property array{name: string, value: string} $overlays
 */
class BulkUpdateCustomOverlaysRequest extends WithDealerRequest
{
    public function getRules(): array
    {
        return array_merge($this->rules, [
            'overlays' => 'array|required',
            'overlays.*.name' => ['required', Rule::in(CustomOverlay::VALID_CUSTOM_NAMES)],
            'overlays.*.value' => 'nullable|string|max:255',
        ]);
    }
}
