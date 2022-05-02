<?php

namespace App\Transformers\Subscription;

use League\Fractal\TransformerAbstract;

class CustomerTransformer extends TransformerAbstract
{
    public function transform($params): array
    {
        return [
            'id' => $params->id,
            'subscriptions' => $params->subscriptions->data ?? [],
            'card' => $params->sources->data ?? []
        ];
    }
}
