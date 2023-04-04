<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryFile;
use App\Models\Inventory\InventoryImage;
use App\Repositories\Repository;
use App\Repositories\TransactionalRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\LazyCollection;

interface InventoryRepositoryInterface extends Repository, TransactionalRepository
{
    const DEFAULT_GET_PARAMS = [
        self::CONDITION_AND_WHERE => [
            ['is_archived', '<>', 1],
        ],
    ];

    public function getAll($params, bool $withDefault = true, bool $paginated = false, $select = []);

    /**
     * Gets the query cursor to avoid memory leaks
     *
     * @param array $params
     * @return LazyCollection
     */
    public function getAllAsCursor(array $params): LazyCollection;

    public function exists(array $params);

    public function getAllWithHavingCount($params, bool $withDefault = true);

    public function getFloorplannedInventory($params);

    /**
     * Gets the query cursor to avoid memory leaks
     *
     * @param array $params
     * @return LazyCollection
     */
    public function getFloorplannedInventoryAsCursor(array $params): LazyCollection;

    public function getPopularInventory(int $dealer_id);

    public function update($params, array $options = []): Inventory;

    public function massUpdate(array $params): bool;

    public function bulkUpdate(array $where, array $params): bool;

    /**
     * @return int number of touched records
     */
    public function moveLocationId(int $from, int $to): int;

    /**
     * Returns data about an inventory item and increments its times viewed
     * counter
     *
     * @param array $params
     * @return Inventory
     */
    public function getAndIncrementTimesViewed(array $params): Inventory;

    /**
     * Archived Inventory units from specific dealer id
     *
     * @param int $dealerId
     * @param array $inventoryParams
     * @return mixed
     */
    public function archiveInventory(int $dealerId, array $inventoryParams);

    /**
     * Find the inventory by stock
     *
     * @param int $dealerId
     * @param string $stock
     * @return Inventory|null
     */
    public function findByStock(int $dealerId, string $stock): ?Inventory;

    /**
     * @param Inventory $inventory
     * @param array $newImages
     * @return InventoryImage[]
     */
    public function createInventoryImages(Inventory $inventory, array $newImages): array;

    /**
     * @param Inventory $inventory
     * @param array $newFiles
     * @return InventoryFile[]
     */
    public function createInventoryFiles(Inventory $inventory, array $newFiles): array;

    /**
     * Get necessary configuration to generate overlays
     *
     * @param  int  $inventoryId
     * @return array{
     *     dealer_id:int,
     *     inventory_id: int,
     *     overlay_logo: string,
     *     overlay_logo_position: string,
     *     overlay_logo_width: int,
     *     overlay_upper: string,
     *     overlay_upper_bg: string,
     *     overlay_upper_alpha: string,
     *     overlay_upper_text: string,
     *     overlay_upper_size: int,
     *     overlay_upper_margin: string,
     *     overlay_lower: string,
     *     overlay_lower_bg: string,
     *     overlay_lower_alpha: string,
     *     overlay_lower_text: string,
     *     overlay_lower_size: int,
     *     overlay_lower_margin: string,
     *     overlay_default: int,
     *     overlay_enabled: int,
     *     dealer_overlay_enabled: int,
     *     overlay_text_dealer: string,
     *     overlay_text_phone: string,
     *     country: string,
     *     overlay_text_location: string,
     *     overlay_updated_at: string
     *     }
     */
    public function getOverlayParams(int $inventoryId): array;

    /**
     * @param  int  $inventoryId
     * @return Collection<InventoryImage>|InventoryImage[] all images related to the inventory
     */
    public function getInventoryImages(int $inventoryId): Collection;

    /**
     * @return bool true when it changed desired image, false when it di not
     */
    public function markImageAsOverlayGenerated(int $imageId): bool;
}
