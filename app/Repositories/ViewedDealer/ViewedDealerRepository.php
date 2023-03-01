<?php

namespace App\Repositories\ViewedDealer;

use App\Domains\ViewedDealer\Actions\CreateViewedDealerAction;
use App\Domains\ViewedDealer\Exceptions\DealerIdExistsException;
use App\Domains\ViewedDealer\Exceptions\DuplicateDealerIdException;
use App\DTOs\Dealer\TcApiResponseDealer;
use App\DTOs\Inventory\TcEsInventory;
use App\Models\Dealer\ViewedDealer;
use App\Services\Dealers\DealerServiceInterface;
use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Throwable;

class ViewedDealerRepository implements ViewedDealerRepositoryInterface
{
    public function __construct(
        private DealerServiceInterface $dealerService,
        private InventoryServiceInterface $inventoryService,
    )
    {
    }

    /**
     * Get the ViewedDealer model by name, returns null if it doesn't exist
     *
     * @param string $name
     * @return ViewedDealer
     * @throws ModelNotFoundException
     */
    public function findByName(string $name): ViewedDealer
    {
        $viewedDealer = ViewedDealer::where('name', $name)->first();

        if ($viewedDealer === null) {
            return $this->createViewedDealerFromTcApi($name);
        }

        return $viewedDealer;
    }

    /**
     * Create new viewed_dealer records (accepts multiple pairs)
     *
     * @param array<int, array{dealer_id: int, name: string}> $params
     * @return array
     * @throws DealerIdExistsException
     * @throws Throwable
     */
    public function create(array $params): array
    {
        return resolve(CreateViewedDealerAction::class)->execute($params);
    }

    /**
     * @param string $name
     * @return ViewedDealer
     * @throws ModelNotFoundException
     */
    private function createViewedDealerFromTcApi(string $name): ViewedDealer
    {
        // Get dealers from TC API
        $dealers = $this->dealerService->listByName($name);

        if (empty($dealers)) {
            throw new ModelNotFoundException("Not found dealer with name $name.");
        }

        /** @var TcApiResponseDealer $dealer */
        $dealer = $dealers->first();

        // Get the first inventory from ES
        $inventories = $this->inventoryService->list([
            'dealer_id' => $dealer->id,
        ]);

        /** @var TcEsInventory $inventory */
        $inventory = $inventories->inventories->first();

        return ViewedDealer::create([
            'name' => $dealer->name,
            'dealer_id' => $dealer->id,
            'inventory_id' => (int) $inventory->id,
        ]);
    }
}
