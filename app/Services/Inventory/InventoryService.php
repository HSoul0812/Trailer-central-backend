<?php

namespace App\Services\Inventory;

use App\Jobs\Files\DeleteS3FilesJob;
use App\Models\Inventory\Inventory;
use App\Repositories\Inventory\FileRepositoryInterface;
use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Repository;
use App\Services\File\FileService;
use App\Services\File\ImageService;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class InventoryService
 * @package App\Services\Inventory
 */
class InventoryService
{
    use DispatchesJobs;

    const OVERLAY_ENABLED_PRIMARY = 1;
    const OVERLAY_ENABLED_ALL = 2;

    const OVERLAY_CODES = [
        self::OVERLAY_ENABLED_PRIMARY,
        self::OVERLAY_ENABLED_ALL,
    ];

    /**
     * @var InventoryRepositoryInterface
     */
    private $inventoryRepository;
    /**
     * @var ImageRepositoryInterface
     */
    private $imageRepository;
    /**
     * @var FileRepositoryInterface
     */
    private $fileRepository;

    /**
     * @var ImageService
     */
    private $imageService;
    /**
     * @var FileService
     */
    private $fileService;

    /**
     * InventoryService constructor.
     * @param InventoryRepositoryInterface $inventoryRepository
     * @param ImageRepositoryInterface $imageRepository
     * @param FileRepositoryInterface $fileRepository
     * @param ImageService $imageService
     * @param FileService $fileService
     */
    public function __construct(
        InventoryRepositoryInterface $inventoryRepository,
        ImageRepositoryInterface $imageRepository,
        FileRepositoryInterface $fileRepository,
        ImageService $imageService,
        FileService $fileService
    ) {
        $this->inventoryRepository = $inventoryRepository;
        $this->imageRepository = $imageRepository;
        $this->fileRepository = $fileRepository;

        $this->imageService = $imageService;
        $this->fileService = $fileService;
    }


    /**
     * @param array $params
     * @return int
     */
    public function create(array $params): int
    {
        try {
            $newImages = $params['new_images'] ?? [];
            $newFiles = $params['new_files'] ?? [];
            $hiddenFiles = $params['hidden_files'] ?? [];

            if (!empty($newImages)) {
                $params['new_images'] = $this->uploadImages($params, 'new_images');
            }

            $newFiles = $params['new_files'] = array_merge($newFiles, $hiddenFiles);
            unset($params['hidden_files']);

            if (!empty($newFiles)) {
                $params['new_files'] = $this->uploadFiles($params, 'new_files');
            }

            $this->inventoryRepository->beginTransaction();

            $inventory = $this->inventoryRepository->create($params);

            if (!$inventory instanceof Inventory) {
                Log::error('Item hasn\'t been created.', ['params' => $params]);
                $this->inventoryRepository->rollbackTransaction();

                return false;
            }

            if (isset($params['add_bill']) && $params['add_bill']) {
                //$this->addBill($params);
            }

            Log::info('Item has been successfully created', ['inventoryId' => $inventory->inventory_id]);
            $this->inventoryRepository->commitTransaction();
        } catch (\Exception $e) {
            print_r($e->getMessage());
            print_r($e->getTraceAsString());
            exit();
            Log::error('Item create error.', $e->getTrace());
            $this->inventoryRepository->rollbackTransaction();

            return false;
        }

        return $inventory->inventory_id;
    }

    /**
     * @param int $inventoryId
     * @return bool
     */
    public function delete(int $inventoryId): bool
    {
        try {
            $imagesToDelete = new Collection;
            $filesToDelete = new Collection;

            $imageParams = [Repository::RELATION_WITH_COUNT => 'inventoryImages'];

            $images = $this->imageRepository->getAllByInventoryId($inventoryId, $imageParams);

            foreach ($images as $image) {
                // We remove images that are related only to the current item.
                if ($image->inventory_images_count === 1) {
                    $imagesToDelete->push($image);
                }
            }

            $fileParams = [Repository::RELATION_WITH_COUNT => 'inventoryFiles'];

            $files = $this->fileRepository->getAllByInventoryId($inventoryId, $fileParams);

            foreach ($files as $file) {
                // We remove files that are related only to the current item.
                if ($file->inventory_files_count === 1) {
                    $filesToDelete->push($file);
                }
            }

            if (!$imagesToDelete->isEmpty()) {
                $imagesFilenames = $imagesToDelete->pluck('filename')->toArray();
                $imageIds = $imagesToDelete->pluck('image_id')->toArray();
            }

            if (!$filesToDelete->isEmpty()) {
                $filesFilenames = $filesToDelete->pluck('path')->toArray();
                $fileIds = $filesToDelete->pluck('id')->toArray();
            }

            $deleteInventoryParams = ['id' => $inventoryId];

            if (isset($imageIds)) {
                $deleteInventoryParams['imageIds'] = $imageIds;
            }

            if (isset($fileIds)) {
                $deleteInventoryParams['fileIds'] = $fileIds;
            }

            $result = $this->inventoryRepository->delete($deleteInventoryParams);

            if (isset($imagesFilenames) && $result) {
                $this->dispatch((new DeleteS3FilesJob($imagesFilenames))->onQueue('files'));
            }

            if (isset($filesFilenames) && $result) {
                $this->dispatch((new DeleteS3FilesJob($filesFilenames))->onQueue('files'));
            }

            Log::info('Item has been successfully deleted', ['inventoryId' => $inventoryId]);
        } catch (\Exception $e) {
            Log::error('Item delete error.', $e->getTrace());
            return false;
        }

        return $result;
    }

    /**
     * @param array $params
     * @return array
     */
    private function addBill(array $params): array
    {
        $billInfo = [
            'vendor_id' => $_POST['b_vendorId'],
            'status' => $_POST['b_status'],
            'doc_num' => $_POST['b_docNum'],
            'received_date' => $_POST['b_receivedDate'],
            'due_date' => $_POST['b_dueDate'],
            'memo' => $_POST['b_memo'],
            'id' => $_POST['b_id'],
            'is_floor_plan' => isset($_POST['b_isFloorPlan']) ? $_POST['b_isFloorPlan'] : 0
        ];
        $vendorId = (int) $billInfo['vendor_id'];
        $trueCost = (float) $inventory['true_cost'];
        $fpBalance = (float) $inventory['fp_balance'];
        $fpVendor = (int) $inventory['fp_vendor'];
        if (!empty($vendorId) || !empty($billInfo['is_floor_plan'])) {
            if (empty($billInfo['id'])) {
                insertBill($inventoryId, $billInfo, $numericalDealerId);
            } else {
                updateBill($inventoryId, $billInfo);
            }
        } else if (
            empty($inventory['bill_id']) &&
            !empty($trueCost) &&
            !empty($fpVendor) &&
            !empty($fpBalance)
        ) {
            $billStatus = $trueCost > $fpBalance ? 'due' : 'paid';
            $billNo = 'fp_auto_' . $inventoryId;
            $query = $pdoDb->query("
                            INSERT INTO qb_bills (dealer_id, total, vendor_id, status, doc_num)
                                VALUES (". $numericalDealerId .", 0, ". $fpVendor .", '". $billStatus ."', '". $billNo ."')
                        ");
            $billId = (int) $pdoDb->lastInsertId();
            $pdoDb->query("UPDATE inventory SET send_to_quickbooks=1, bill_id={$billId}, is_floorplan_bill=1 WHERE inventory_id={$inventoryId}");
        }
    }

    /**
     * The method removes stock item duplicates. A not archived item is kept, the one that was created/updated last.
     *
     * @param int $dealerId
     * @return array
     */
    public function deleteDuplicates(int $dealerId): array
    {
        $deletedDuplicates = 0;
        $couldNotDeleteDuplicates = [];

        $params = [
            InventoryRepositoryInterface::SELECT => ['stock'],
            InventoryRepositoryInterface::CONDITION_AND_WHERE => [['dealer_id', '=', $dealerId]],
            InventoryRepositoryInterface::CONDITION_AND_HAVING_COUNT => ['inventory_id', '>', 1],
            InventoryRepositoryInterface::GROUP_BY => ['stock'],
        ];

        $inventory = $this->inventoryRepository->getAllWithHavingCount($params, false);

        if ($inventory->isEmpty()) {
            return compact(['deletedDuplicates', 'couldNotDeleteDuplicates']);
        }

        $duplicatedInventoryParams = [
            InventoryRepositoryInterface::CONDITION_AND_WHERE => [['dealer_id', '=', $dealerId]],
            InventoryRepositoryInterface::CONDITION_AND_WHERE_IN => ['stock' => $inventory->pluck('stock')->toArray()],
        ];

        $duplicatedInventory = $this->inventoryRepository->getAll($duplicatedInventoryParams, false);

        /** @var Collection $duplicateInventory */
        foreach ($duplicatedInventory->groupBy('stock') as $duplicateInventory) {
            if ($duplicateInventory->count() < 2) {
                continue;
            }

            $filteredInventory = $duplicateInventory->filter(function ($item, $key) {
                return $item->is_archived != 1;
            });

            if ($filteredInventory->isEmpty()) {
                $filteredInventory = $duplicateInventory;
            }

            $maxUpdatedAt = $filteredInventory->sortByDesc('updated_at')->first();
            $maxCreatedAt = $filteredInventory->sortByDesc('created_at')->first();

            $notDeletingItem = $maxUpdatedAt->updated_at > $maxCreatedAt->created_at ? $maxUpdatedAt : $maxCreatedAt;

            $itemsToDelete = $duplicateInventory->reject(function ($item, $key) use ($notDeletingItem) {
                return $item->inventory_id === $notDeletingItem->inventory_id;
            });

            foreach ($itemsToDelete as $itemToDelete) {
                $result = $this->delete($itemToDelete->inventory_id);

                if ($result) {
                    $deletedDuplicates++;
                } else {
                    $couldNotDeleteDuplicates[] = $itemToDelete->inventory_id;
                }
            }
        }

        return compact(['deletedDuplicates', 'couldNotDeleteDuplicates']);
    }

    /**
     * @param array $params
     * @param string $imagesKey
     * @return array
     *
     * @throws \App\Exceptions\File\FileUploadException
     * @throws \App\Exceptions\File\ImageUploadException
     */
    private function uploadImages(array $params, string $imagesKey): array
    {
        $images = $params[$imagesKey];

        $isOverlayEnabled = isset($params['overlay_enabled']) && in_array($params['overlay_enabled'], self::OVERLAY_CODES);
        $overlayEnabledParams = ['overlayText' => $params['stock']];

        $withOverlay = [];
        $withoutOverlay = [];

        if ($isOverlayEnabled && $params['overlay_enabled'] == self::OVERLAY_ENABLED_ALL) {
            $withOverlay = $images;

        } elseif ($isOverlayEnabled && $params['overlay_enabled'] == self::OVERLAY_ENABLED_PRIMARY) {
            $withOverlay = array_filter($images, function ($image) {
                return isset($image['position']) && $image['position'] == 0;
            });

            $withoutOverlay = array_filter($images, function ($image) {
                return !isset($image['position']) || $image['position'] != 0;
            });

        } else {
            $withoutOverlay = $images;
        }

        foreach ($withoutOverlay as &$image) {
            $result = $this->imageService->upload($image['url'], $params['title'], $params['dealer_id']);

            $image['filename'] = $result['path'];
            $image['filename_noverlay'] = '';
            $image['hash'] = $result['hash'];
        }

        foreach ($withOverlay as &$image) {
            $noOverlayResult = $this->imageService->upload($image['url'], $params['title'], $params['dealer_id']);
            $overlayResult = $this->imageService->upload($image['url'], $params['title'], $params['dealer_id'], null, $overlayEnabledParams);

            $image['filename'] = $overlayResult['path'];
            $image['filename_noverlay'] = $noOverlayResult['path'];
            $image['hash'] = $overlayResult['hash'];
        }

        return array_merge($withOverlay, $withoutOverlay);
    }

    /**
     * @param array $params
     * @param string $filesKey
     * @return array
     * @throws \App\Exceptions\File\FileUploadException
     */
    private function uploadFiles(array $params, string $filesKey): array
    {
        $files = $params[$filesKey];

        foreach ($files as &$file) {
            $result = $this->fileService->upload($file['url'], $file['title'], $params['dealer_id']);

            $file['path'] = $result['path'];
            $file['type'] = $result['type'];
        }

        return $files;
    }
}
