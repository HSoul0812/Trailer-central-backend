<?php

namespace App\Transformers\Inventory;

use App\Models\Inventory\InventoryFeatureList;
use League\Fractal\TransformerAbstract;

/**
 * Class FeatureTransformer
 * @package App\Transformers\Inventory
 */
class FeatureListTransformer extends TransformerAbstract
{
    /**
     * @param InventoryFeatureList $featureList
     * @return array
     */
    public function transform(InventoryFeatureList $featureList): array
    {
        return [
            'feature_list_id' => $featureList->feature_list_id,
            'feature_name' => $featureList->feature_name,
            'options' => $featureList->available_options
        ];
    }

}
