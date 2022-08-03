<?php

namespace App\Transformers\Parts;

use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrderPart;
use App\Models\Parts\Textrail\PartAttribute;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Primitive;

class PartAttributeTransformer extends TransformerAbstract
{
    public function transform(PartAttribute $partAttribute)
    {
        return [
            'id' => $partAttribute->id,
            'attribute' => $partAttribute->attribute,
            'value' => $partAttribute->attribute_value
        ];
    }
}
