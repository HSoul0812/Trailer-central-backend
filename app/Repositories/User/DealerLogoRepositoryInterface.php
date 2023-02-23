<?php

namespace App\Repositories\User;

use App\Models\User\DealerLogo;

interface DealerLogoRepositoryInterface
{
    public function get(int $dealerId): ?DealerLogo;

    public function update(int $dealerId, array $params): DealerLogo;
}
