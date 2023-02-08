<?php

namespace App\Services\Inventory;

use App\Jobs\Inventory\InventoryBulkUpdateManufacturer;
use App\Repositories\Inventory\InventoryBulkUpdateRepository;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Repositories\Showroom\ShowroomBulkUpdateRepository;

/**
 * class InventoryBulkUpdateManufacturerService
 *
 * @package App\Services\Inventory
 */
class InventoryBulkUpdateManufacturerService implements InventoryBulkUpdateManufacturerServiceInterface
{
    /**
     * @var array
     */
    private $params;

    /**
     * @var InventoryBulkUpdateRepository
     */
    private $inventoryBulkUpdateRepository;

    /**
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
        $this->inventoryBulkUpdateRepository = new InventoryBulkUpdateRepository();
    }

    /**
     * {@inheritDoc}
     */
    public function update()
    {
        try {
            $inventories = $this->inventoryBulkUpdateRepository->getInventoriesFromManufacturer([
                'manufacturer' => $this->params['manufacturer']
            ]);

            foreach ($inventories as $inventory) {
                $this->inventoryBulkUpdateRepository->bulkUpdateInventoryManufacturer(
                    $inventory,
                    [
                        'manufacturer' => $this->params['to_manufacturer']
                    ]
                );
            }

            Log::info('Inventory manufacturers updated successfully', $this->params);
        } catch (Exception $e) {
            Log::error('Inventory manufacturers update error. Message - ' . $e->getMessage(), $e->getTrace());

            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function bulkUpdateManufacturer($params)
    {
        return dispatch((new InventoryBulkUpdateManufacturer($params))->onQueue('inventory'));
    }
}
