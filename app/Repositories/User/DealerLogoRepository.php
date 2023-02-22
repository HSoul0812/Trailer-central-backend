<?php

namespace App\Repositories\User;

use App\Models\User\DealerLogo;

class DealerLogoRepository implements DealerLogoRepositoryInterface
{
    public function get(int $dealerId): DealerLogo
    {
        return DealerLogo::whereDealerId($dealerId)->firstOrFail();
    }

    public function delete(int $dealerId): void
    {
        DealerLogo::whereDealerId($dealerId)->delete();
    }

    public function create(array $params): DealerLogo
    {
        return DealerLogo::create($params);
    }

    public function update(int $dealerId, array $params): void
    {
        DealerLogo::whereDealerId($dealerId)->update($params);
    }
}
