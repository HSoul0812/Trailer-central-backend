<?php

namespace App\Repositories\User;

use App\Models\User\DealerLogo;

interface DealerLogoRepositoryInterface
{
    public function get(int $dealerId): DealerLogo;

    public function delete(int $dealerId): void;

    public function create(array $params): DealerLogo;

    public function update(int $dealerId, array $params): void;
}
