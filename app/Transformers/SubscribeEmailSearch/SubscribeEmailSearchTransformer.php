<?php

declare(strict_types=1);

namespace App\Transformers\SubscribeEmailSearch;

use League\Fractal\TransformerAbstract;

class SubscribeEmailSearchTransformer extends TransformerAbstract
{
    public function transform($subscribeEmail): array
    {
        return [
             'id' => (int) $subscribeEmail->id,
             'email' => $subscribeEmail->email,
             'url' => $subscribeEmail->url,
             'subscribe_email_sent' => $subscribeEmail->subscribe_email_sent,
             'created_at' => $subscribeEmail->created_at,
         ];
    }
}
