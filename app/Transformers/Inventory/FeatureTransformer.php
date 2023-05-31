<?php

namespace App\Transformers\Inventory;

use App\Models\Inventory\InventoryFeature;
use League\Fractal\TransformerAbstract;

/**
 * Class FeatureTransformer
 * @package App\Transformers\Inventory
 */
class FeatureTransformer extends TransformerAbstract
{
    /**
     * @param InventoryFeature $feature
     * @return array
     */
    public function transform(InventoryFeature $feature): array
    {
        return [
            'feature_list_id' => $feature->feature_list_id,
            'value' => trim($feature->value),
            'feature_name' => $feature->featureList ? $feature->featureList->feature_name : '',
        ];
    }
}
