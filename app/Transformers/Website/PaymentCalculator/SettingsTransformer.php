<?php

namespace App\Transformers\Website\PaymentCalculator;

use Illuminate\Support\Arr;
use League\Fractal\TransformerAbstract;
use App\Models\Website\PaymentCalculator\Settings;

class SettingsTransformer extends TransformerAbstract
{
    public function transform(Settings $settings): array
    {
        return [
            'id' => (int)$settings->id,
            'apr' => $settings->apr,
            'down' => $settings->down,
            'months' => $settings->months,
            'entity_type_id' => $settings->entity_type_id,
            'operator' => $settings->operator,
            'inventory_price' => $settings->inventory_price,
            // to avoid breaking changes at legacy dashboard we should continue using `condition` instead `inventory_condition`
            // @todo remove condition property when legacy dashboard has been dropped
            'condition' => $settings->inventory_condition,
            'inventory_condition' => $settings->inventory_condition,
            'financing' => $settings->financing,
            'entity' => Arr::only($settings->entityType->toArray(), ['name', 'title'])
        ];
    }
}
