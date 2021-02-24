<?php

namespace App\Transformers\User;

use App\Models\User\Settings;
use League\Fractal\TransformerAbstract;

class SettingsTransformer extends TransformerAbstract 
{
    public function transform(Settings $settings)
    {
        return [
            'dealer_id' => $settings->dealer_id,
            'setting' => $settings->setting,
            'value' => $settings->setting_value
        ];
    }
}
