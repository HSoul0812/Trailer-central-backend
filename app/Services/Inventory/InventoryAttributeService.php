<?php

namespace App\Services\Inventory;

use App\Exceptions\Inventory\InventoryException;
use App\Models\Inventory\Inventory;
use App\Repositories\Inventory\AttributeValueRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 * Class InventoryAttributeService
 *
 * @package App\Services\Inventory
 */
class InventoryAttributeService implements InventoryAttributeServiceInterface
{
    /**
     * @var InventoryRepositoryInterface
     */
    private $inventoryRepository;

    /**
     * @var AttributeValueRepositoryInterface
     */
    private $attributeValueRepository;

    /**
     * @param InventoryRepositoryInterface $inventoryRepository
     * @param AttributeValueRepositoryInterface $attributeValueRepository
     */
    public function __construct(
        InventoryRepositoryInterface $inventoryRepository,
        AttributeValueRepositoryInterface $attributeValueRepository
    ) {
        $this->inventoryRepository = $inventoryRepository;
        $this->attributeValueRepository = $attributeValueRepository;
    }

    /**
     * @param array $params
     *
     * @return Inventory
     *
     * @throws InventoryException
     */
    public function update(array $params): Inventory
    {
        try {
            $inventory = $this->inventoryRepository->get([
                'id' => $params['inventory_id'],
                'dealer_id' => $params['dealer_id'],
            ]);

            $options = [
                'inventory_id' => $params['inventory_id'],
            ];

            foreach ($params['attributes'] as $key => $value) {
                $this->attributeValueRepository->updateOrCreate(
                    [
                        'value' => $value['value'],
                    ],
                    [
                        'attribute_id' => $value['id'],
                    ] + $options
                );
            }

            Log::info('Item has been successfully updated', $options);
        } catch (\Exception $e) {
            Log::error('Item update error. Message - ' . $e->getMessage(), $e->getTrace());

            throw new InventoryException('Inventory item update error');
        }

        return $inventory;
    }
}
