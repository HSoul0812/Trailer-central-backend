<?php

namespace App\Transformers\Integration;

use App\Models\Integration\Collector\CollectorFields;
use League\Fractal\TransformerAbstract;

/**
 * Class CollectorFieldsTransformer
 * @package App\Transformers\Integration
 */
class CollectorFieldsTransformer extends TransformerAbstract
{
    /**
     * @param CollectorFields $collectorFields
     * @return array
     */
    public function transform(CollectorFields $collectorFields)
    {
        return [
            'id' => $collectorFields->id,
            'field' => $collectorFields->field,
            'label' => $collectorFields->label,
            'type' => $collectorFields->type,
            'mapped' => $collectorFields->mapped,
            'boolean' => $collectorFields->boolean,
        ];
    }
}
