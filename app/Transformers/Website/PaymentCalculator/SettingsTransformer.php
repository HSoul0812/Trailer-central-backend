<?php

namespace App\Transformers\Website\PaymentCalculator;

use League\Fractal\TransformerAbstract;
use App\Models\Website\PaymentCalculator\Settings;

class SettingsTransformer extends TransformerAbstract
{
    public function transform(Settings $settings)
    {                        
	 return [
             'id' => (int)$settings->id,
             'apr' => $settings->apr,
             'down' => $settings->down,
             'months' => $settings->months,
             'entity_type_id' => $settings->entity_type_id,
             'operator' => $settings->operator,
             'inventory_price' => $settings->inventory_price
         ];
    }
}