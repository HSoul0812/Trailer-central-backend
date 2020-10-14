<?php


namespace App\Transformers\Dms;


use App\Models\CRM\Dms\Settings;
use League\Fractal\TransformerAbstract;

class SettingsTransformer extends TransformerAbstract
{
    public function transform(Settings $settings)
    {
        return $settings->toArray();
    }
}
