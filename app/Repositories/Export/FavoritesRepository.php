<?php

namespace App\Repositories\Export;

use App\Models\Website\User\WebsiteUser;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class FavoritesRepository implements FavoritesRepositoryInterface
{
    /**
     * @param array $params
     * @return Collection
     * @throws InvalidArgumentException if `website_id` is not available
     */
    public function get(array $params): Collection
    {
        if (!isset($params['website_id'])) {
            throw new InvalidArgumentException("A website id is required");
        }

        return WebsiteUser::where('website_id', $params['website_id'])->with([
            'favoriteInventories.inventory.dealerLocation',
            'favoriteInventories.inventory.entityType',
        ])->get();
    }
}
