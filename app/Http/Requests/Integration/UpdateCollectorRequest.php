<?php

namespace App\Http\Requests\Integration;

use App\Http\Requests\Request;
use App\Models\Integration\Collector\Collector;
use Illuminate\Validation\Rule;

/**
 * Class UpdateCollectorRequest
 * @package App\Http\Requests\Integration
 */
class UpdateCollectorRequest extends Request
{
    /**
     * @return array
     */
    protected function getRules(): array
    {
        return [
            'dealer_id' => 'integer|min:1|exists:dealer,dealer_id',
            'id' => 'integer|min:1|collector_valid',
            'override_all' => [Rule::in([Collector::OVERRIDE_NOT_SET, Collector::OVERRIDE_UNLOCKED, Collector::OVERRIDE_LOCKED])],
            'override_images' => [Rule::in([Collector::OVERRIDE_NOT_SET, Collector::OVERRIDE_UNLOCKED, Collector::OVERRIDE_LOCKED])],
            'override_video' => [Rule::in([Collector::OVERRIDE_NOT_SET, Collector::OVERRIDE_UNLOCKED, Collector::OVERRIDE_LOCKED])],
            'override_prices' => [Rule::in([Collector::OVERRIDE_NOT_SET, Collector::OVERRIDE_UNLOCKED, Collector::OVERRIDE_LOCKED])],
            'override_attributes' => [Rule::in([Collector::OVERRIDE_NOT_SET, Collector::OVERRIDE_UNLOCKED, Collector::OVERRIDE_LOCKED])],
            'override_descriptions' => [Rule::in([Collector::OVERRIDE_NOT_SET, Collector::OVERRIDE_UNLOCKED, Collector::OVERRIDE_LOCKED])],
        ];
    }
}
