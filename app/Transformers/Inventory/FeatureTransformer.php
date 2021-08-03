<?php

namespace App\Transformers\Inventory;

use App\Models\Inventory\InventoryFeatureList;
use League\Fractal\TransformerAbstract;

/**
 * Class FeatureTransformer
 * @package App\Transformers\Inventory
 */
class FeatureTransformer extends TransformerAbstract
{
    /**
     * @param Attribute $attribute
     * @return array
     */
    public function transform(InventoryFeatureList $featureList)
    {
        return [
            'feature_list_id' => $featureList->feature_list_id,
            'feature_name' => $featureList->feature_name,
            'options' => $featureList->available_options
        ];
    }

}
