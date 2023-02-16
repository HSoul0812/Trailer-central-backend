<?php

namespace App\Repositories\ViewedDealer;

use App\Domains\ViewedDealer\Actions\CreateViewedDealerAction;
use App\Domains\ViewedDealer\Exceptions\DealerIdExistsException;
use App\Domains\ViewedDealer\Exceptions\DuplicateDealerIdException;
use App\Models\Dealer\ViewedDealer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class ViewedDealerRepository implements ViewedDealerRepositoryInterface
{
    /**
     * Get the ViewedDealer model by name, returns null if it doesn't exist
     *
     * @param string $name
     * @return ViewedDealer
     * @throws ModelNotFoundException
     */
    public function findByName(string $name): ViewedDealer
    {
        return ViewedDealer::where('name', $name)->firstOrFail();
    }

    /**
     * Create new viewed_dealer records (accepts multiple pairs)
     *
     * @param array<int, array{dealer_id: int, name: string}> $params
     * @return Collection
     * @throws DuplicateDealerIdException
     * @throws DealerIdExistsException
     */
    public function create(array $params): Collection
    {
        return resolve(CreateViewedDealerAction::class)->execute($params);
    }
}
