<?php

namespace App\Repositories\Export;

use Illuminate\Support\Collection;

interface FavoritesRepositoryInterface
{
    /**
     * @param array $params
     * @return Collection
     */
    public function get(array $params): Collection;
}
