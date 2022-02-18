<?php

namespace App\Transformers\Dispatch\Facebook;

use App\Services\Dispatch\Facebook\DTOs\MarketplaceStep;
use League\Fractal\TransformerAbstract;

class StepTransformer extends TransformerAbstract
{
    public function transform(MarketplaceStep $step)
    {
        return [
            'step' => $step->step,
            'action' => $step->action,
            'inventory_id' => $step->inventoryId,
            'status' => $step->getResponse(),
            'selectors' => $step->getSelectors()
        ];
    }
}