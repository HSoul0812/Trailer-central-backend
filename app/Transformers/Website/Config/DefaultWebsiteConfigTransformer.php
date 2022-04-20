<?php

namespace App\Transformers\Website\Config;

use App\Models\Website\Config\WebsiteConfigDefault;
use League\Fractal\TransformerAbstract;

class DefaultWebsiteConfigTransformer extends TransformerAbstract
{

    public function transform(WebsiteConfigDefault $config)
    {
        return [
            "key" => $config->key,
            "private" => $config->private,
            "type" => $config->type,
            "label" => $config->label,
            "note" => $config->note,
            "grouping" => $config->grouping,
            "values" => $config->values,
            "values_mapping" => $config->values_mapping,
            "default_label" => $config->default_label,
            "default_value" => $config->default_value,
            "sort_order" => $config->sort_order,
        ];
    }
}
