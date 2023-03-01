<?php

namespace App\Transformers\User;

use App\Models\User\DealerLogo;
use App\Services\User\DealerLogoService;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;

class DealerLogoTransformer extends TransformerAbstract
{
    public function transform(DealerLogo $dealerLogo): array
    {
        return [
            'id' => $dealerLogo->id,
            'dealer_id' => $dealerLogo->dealer_id,
            'url' => $dealerLogo->filename ? Storage::disk(DealerLogoService::STORAGE_DISK)->url($dealerLogo->filename) : null,
            'benefit_statement' => $dealerLogo->benefit_statement
        ];
    }
}
