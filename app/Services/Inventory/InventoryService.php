<?php

namespace App\Services\Inventory;

use App\Helpers\ArrayHelper;
use App\Jobs\Files\DeleteS3FilesJob;
use App\Models\CRM\Dms\Quickbooks\Bill;
use App\Models\Inventory\Inventory;
use App\Models\User\Interfaces\PermissionsInterface as Permissions;
use App\Repositories\Dms\Quickbooks\BillRepositoryInterface;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepositoryInterface;
use App\Repositories\Inventory\FileRepositoryInterface;
use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Repository;
use App\Services\File\FileService;
use App\Services\File\ImageService;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Class InventoryService
 * @package App\Services\Inventory
 */
class InventoryService
{
    use DispatchesJobs;

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
     * @var BillRepositoryInterface
     */
    private $billRepository;
    /**
     * @var QuickbookApprovalRepositoryInterface
     */
    private $quickbookApprovalRepository;

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
     * @param BillRepositoryInterface $billRepository
     * @param QuickbookApprovalRepositoryInterface $quickbookApprovalRepository
     * @param ImageService $imageService
     * @param FileService $fileService
     */
    public function __construct(
        InventoryRepositoryInterface $inventoryRepository,
        ImageRepositoryInterface $imageRepository,
        FileRepositoryInterface $fileRepository,
        BillRepositoryInterface $billRepository,
        QuickbookApprovalRepositoryInterface $quickbookApprovalRepository,
        ImageService $imageService,
        FileService $fileService
    ) {
        $this->inventoryRepository = $inventoryRepository;
        $this->imageRepository = $imageRepository;
        $this->fileRepository = $fileRepository;
        $this->billRepository = $billRepository;
        $this->quickbookApprovalRepository = $quickbookApprovalRepository;

        $this->imageService = $imageService;
        $this->fileService = $fileService;
    }


    /**
     * @param array $params
     * @return Inventory|null
     */
    public function create(array $params): ?Inventory
    {
        try {
            $newImages = $params['new_images'] ?? [];
            $newFiles = $params['new_files'] ?? [];
            $hiddenFiles = $params['hidden_files'] ?? [];
            $clappsDefaultImage = $params['clapps']['default-image']['url'] ?? '';

            $addBill = $params['add_bill'] ?? false;

            if (!empty($newImages)) {
                $params['new_images'] = $this->uploadImages($params, 'new_images');
            }

            $newFiles = $params['new_files'] = array_merge($newFiles, $hiddenFiles);
            unset($params['hidden_files']);

            if (!empty($newFiles)) {
                $params['new_files'] = $this->uploadFiles($params, 'new_files');
            }

            if (!empty($clappsDefaultImage)) {
                $clappImage = $this->imageService->upload($clappsDefaultImage, $params['title'], $params['dealer_id']);
                $params['clapps']['default-image'] = $clappImage['path'];
            }

            $this->inventoryRepository->beginTransaction();

            $inventory = $this->inventoryRepository->create($params);

            if (!$inventory instanceof Inventory) {
                Log::error('Item hasn\'t been created.', ['params' => $params]);
                $this->inventoryRepository->rollbackTransaction();

                return null;
            }

            if ($addBill) {
                $this->addBill($params, $inventory);
            }

            $this->inventoryRepository->commitTransaction();

            Log::info('Item has been successfully created', ['inventoryId' => $inventory->inventory_id]);
        } catch (\Exception $e) {
            Log::error('Item create error.', $e->getTrace());
            $this->inventoryRepository->rollbackTransaction();

            return null;
        }

        return $inventory;
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

        $isOverlayEnabled = isset($params['overlay_enabled']) && in_array($params['overlay_enabled'], Inventory::OVERLAY_CODES);
        $overlayEnabledParams = ['overlayText' => $params['stock']];

        $withOverlay = [];
        $withoutOverlay = [];

        if ($isOverlayEnabled && $params['overlay_enabled'] == Inventory::OVERLAY_ENABLED_ALL) {
            $withOverlay = $images;

        } elseif ($isOverlayEnabled && $params['overlay_enabled'] == Inventory::OVERLAY_ENABLED_PRIMARY) {
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

    /**
     * @param array $params
     * @param Inventory $inventory
     * @return void
     *
     * Get current inventory and check if it's floor planned
     * if so, we have to auto-generate a new bill and associate this inventory to a new bill
     *
     * @todo I believe it should be an event
     */
    private function addBill(array $params, Inventory $inventory): void
    {
        $billInfo = [
            'dealer_id' => $inventory->dealer_id,
            'inventory_id' => $inventory->inventory_id,
            'vendor_id' => $params['b_vendorId'] ?? null,
            'status' => $params['b_status'] ?? null,
            'doc_num' => $params['b_docNum'] ?? null,
            'received_date' => $params['b_receivedDate'] ?? null,
            'due_date' => $params['b_dueDate'] ?? null,
            'memo' => $params['b_memo'] ?? null,
            'id' => $params['b_id'] ?? null,
            'is_floor_plan' => $params['b_isFloorPlan'] ?? 0
        ];

        $vendorId = (int)$billInfo['vendor_id'];
        $trueCost = $inventory->true_cost;
        $fpBalance = $inventory->fp_balance;
        $fpVendor = (int)$inventory['fp_vendor'];

        if (!empty($vendorId) || !empty($billInfo['is_floor_plan'])) {
            if (empty($billInfo['id'])) {
                $billInfo['total'] = 0;
                $bill = $this->billRepository->create($billInfo);

                $inventoryParams = [
                    'inventory_id' => $inventory->inventory_id,
                    'send_to_quickbooks' => 1,
                    'bill_id' => $bill->id,
                    'is_floorplan_bill'=> $billInfo['is_floor_plan']
                ];

                $this->inventoryRepository->update($inventoryParams);
            } else {
                $bill = $this->billRepository->update($billInfo);

                $this->quickbookApprovalRepository->deleteByTbPrimaryId($bill->id);

                $inventoryParams = [
                    'inventory_id' => $inventory->inventory_id,
                    'send_to_quickbooks' => 1,
                    'bill_id' => $bill->id,
                    'is_floorplan_bill' => $billInfo['is_floor_plan'],
                    'qb_sync_processed' => 0,
                ];

                $this->inventoryRepository->update($inventoryParams);
            }

        } else if (empty($inventory->bill_id) && !empty($trueCost) && !empty($fpVendor) && !empty($fpBalance)) {
            $billStatus = $trueCost > $fpBalance ? Bill::STATUS_DUE : Bill::STATUS_PAID;
            $billNo = 'fp_auto_' . $inventory->inventory_id;

            $billParams = [
                'dealer_id' => $inventory->dealer_id,
                'total' => 0,
                'vendor_id' => $params['b_vendorId'],
                'status' => $billStatus,
                'doc_num' => $billNo
            ];

            $bill = $this->billRepository->create($billParams);

            $inventoryParams = [
                'inventory_id' => $inventory->inventory_id,
                'send_to_quickbooks' => 1,
                'bill_id' => $bill->id,
                'is_floorplan_bill'=> $billInfo['is_floor_plan']
            ];

            $this->inventoryRepository->update($inventoryParams);
        }
    }
}
