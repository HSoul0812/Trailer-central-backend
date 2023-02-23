<?php

namespace App\Repositories\User;

use App\Models\User\DealerLogo;

class DealerLogoRepository implements DealerLogoRepositoryInterface
{
    public function get(int $dealerId): ?DealerLogo
    {
        return DealerLogo::whereDealerId($dealerId)->first();
    }

    public function update(int $dealerId, array $params): DealerLogo
    {
        return DealerLogo::updateOrCreate(['dealer_id' => $dealerId], $params);
    }
}
