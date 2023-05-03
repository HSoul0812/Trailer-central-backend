<?php

declare(strict_types=1);

namespace App\Repositories\SubscribeEmailSearch;

use App\Models\SubscribeEmailSearch\SubscribeEmailSearch;

interface SubscribeEmailSearchRepositoryInterface
{
    public function create($params): SubscribeEmailSearch;
}
