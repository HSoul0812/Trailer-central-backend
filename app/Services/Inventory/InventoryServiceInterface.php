<?php

namespace App\Services\Inventory;

use App\Models\Inventory\File;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryImage;

/**
 * Interface InventoryServiceInterface
 * @package App\Services\Inventory
 */
interface InventoryServiceInterface
{
    /**
     * @param array $params
     * @return Inventory
     */
    public function create(array $params): Inventory;

    /**
     * @param array $params
     * @return Inventory
     */
    public function update(array $params): Inventory;

    /**
     * @param array $params
     * @return bool
     */
    public function massUpdate(array $params): bool;

    /**
     * @param int $inventoryId
     * @return bool
     */
    public function delete(int $inventoryId): bool;

    /**
     * @param int $dealerId
     * @return array
     */
    public function deleteDuplicates(int $dealerId): array;

    /**
     * @return array
     */
    public function archiveSoldItems(): array;

    /**
     * @param int $inventoryId
     * @param string $toZip
     * @return float
     */
    public function deliveryPrice(int $inventoryId, string $toZip): float;

    /**
     * @param int $inventoryId
     * @param array $params
     * @return InventoryImage
     */
    public function createImage(int $inventoryId, array $params): InventoryImage;

    /**
     * @param int $inventoryId
     * @param array $params
     * @return File
     */
    public function createFile(int $inventoryId, array $params): File;

    /**
     * Deletes the inventory images from the DB and the filesystem
     *
     * @param int $inventoryId
     * @param int[] $imageIds
     * @return bool
     */
    public function imageBulkDelete(int $inventoryId, array $imageIds = null): bool;

    /**
     * @param int $inventoryId
     * @return bool
     */
    public function fileBulkDelete(int $inventoryId): bool;

    /**
     * Exports an inventory and returns the url to the export
     *
     * @param int $inventoryId
     * @param string $format
     * @return string
     */
    public function export(int $inventoryId, string $format): string;

    /**
     * @param string $markDown
     * @return string
     */
    public function convertMarkdown(string $markDown): string;
}
