<?php

namespace App\Services\Inventory;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Jobs\Inventory\InventoryBulkUpdateManufacturer;
use App\Repositories\Inventory\InventoryRepositoryInterface;

/**
 * class InventoryBulkUpdateManufacturerService
 *
 * @package App\Services\Inventory
 */
class InventoryBulkUpdateManufacturerService implements InventoryBulkUpdateManufacturerServiceInterface
{

    /**
     * @var InventoryRepositoryInterface
     */
    private $inventoryRepository;

    /**
     * @param InventoryRepositoryInterface $inventoryRepository
     */
    public function __construct(
        InventoryRepositoryInterface $inventoryRepository
    )
    {
        $this->inventoryRepository = $inventoryRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function update(array $params)
    {
        try {
            $this->inventoryRepository->bulkUpdate(
                ['manufacturer' => $params['from_manufacturer']],
                [ 'manufacturer' => $params['to_manufacturer']]
            );

            Log::info('Inventory manufacturers updated successfully', $params);
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
        dispatch((new InventoryBulkUpdateManufacturer($params))->onQueue('inventory'));
    }
}
