<?php

namespace App\Transformers\User;

use App\Models\User\DealerLogo;
use League\Fractal\TransformerAbstract;

class DealerLogoTransformer extends TransformerAbstract
{
    public function transform(DealerLogo $dealerLogo): array
    {
        return [
            'id' => $dealerLogo->id,
            'dealer_id' => $dealerLogo->dealer_id,
            'filename' => sprintf('https://%s.s3.amazonaws.com/%s', env('AWS_BUCKET'), $dealerLogo->filename),
            'benefit_statement' => $dealerLogo->benefit_statement
        ];
    }
}
