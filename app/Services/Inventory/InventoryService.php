<?php

namespace App\Services\Inventory;

use App\Events\Inventory\InventoryDeletedEvent;
use App\Repositories\Inventory\InventoryRepositoryInterface;

/**
 * Class InventoryService
 * @package App\Services\Inventory
 */
class InventoryService
{
    /**
     * @var InventoryRepositoryInterface
     */
    private $inventoryRepository;

    /**
     * InventoryService constructor.
     * @param InventoryRepositoryInterface $inventoryRepository
     */
    public function __construct(InventoryRepositoryInterface $inventoryRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function getAll($params)
    {
        return $this->inventoryRepository->getAll($params, true, true);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function get(int $id)
    {
        return $this->inventoryRepository->get(['id' => $id]);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        $item = $this->inventoryRepository->get(['id' => $id]);

        $result = $this->inventoryRepository->delete(['id' => $id]);

        if ($result) {
            event(new InventoryDeletedEvent($item));
        }

        return $result;
    }
}
