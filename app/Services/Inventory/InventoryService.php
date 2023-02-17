<?php

namespace App\Services\Inventory;

use App\Contracts\LoggerServiceInterface;
use App\Exceptions\File\FileUploadException;
use App\Exceptions\File\ImageUploadException;
use App\Exceptions\Inventory\InventoryException;
use App\Jobs\Inventory\ReIndexInventoriesByDealerLocationJob;
use App\Jobs\Website\ReIndexInventoriesByDealersJob;
use App\Models\CRM\Dms\Quickbooks\Bill;
use App\Models\Inventory\File;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryImage;
use App\Models\User\DealerLocation;
use App\Models\Website\Config\WebsiteConfig;
use App\Repositories\Dms\Quickbooks\BillRepositoryInterface;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepositoryInterface;
use App\Repositories\Inventory\CategoryRepositoryInterface;
use App\Repositories\Inventory\FileRepositoryInterface;
use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Repository;
use App\Repositories\User\DealerLocationMileageFeeRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;
use App\Services\File\FileService;
use App\Services\File\ImageService;
use App\Services\User\GeoLocationServiceInterface;
use App\Transformers\Inventory\InventoryTitleAndVinTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use League\Fractal\Resource\Collection as FractalResourceCollection;
use League\Fractal\Manager as FractalManager;
use App\Repositories\Dms\Customer\InventoryRepository as DmsCustomerInventoryRepository;
use App\Services\Export\Inventory\PdfExporter;
use App\Traits\S3\S3Helper;
use App\Jobs\Inventory\GenerateOverlayImageJob;

/**
 * Class InventoryService
 * @package App\Services\Inventory
 */
class InventoryService implements InventoryServiceInterface
{
    use DispatchesJobs, S3Helper;

    const SOURCE_DASHBOARD = 'dashboard';

    const PDF_EXPORT = 'pdf';

    private const RESOURCE_KEY = 'children';
    private const OPTION_GROUP_TEXT_CUSTOMER_OWNED = 'Customer Owned Inventories';
    private const OPTION_GROUP_TEXT_DEALER_OWNED = 'All Inventories';

    private const CHANGED_FIELDS_IN_DASHBOARD_UNLOCK_MAPPING = [
        'unlock_type_code' => [
            'category',
            'category_label',
        ],
        'unlock_designation' => [
            'condition'
        ],
        'dealer_location' => [
            'dealer_location_id'
        ],
        'unlock_images' => [
            'existing_images'
        ],
        'unlock_files' => [
            'existing_files'
        ],
        'unlock_video' => [
            'video_embed_code'
        ],
        'unlock_length' => [
            'length_second',
            'length_inches_second',
        ],
        'unlock_width' => [
            'width_second',
            'width_inches_second',
        ],
        'unlock_height' => [
            'height_second',
            'height_inches_second',
        ],
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
     * @var BillRepositoryInterface
     */
    private $billRepository;
    /**
     * @var QuickbookApprovalRepositoryInterface
     */
    private $quickbookApprovalRepository;
    /**
     * @var WebsiteConfigRepositoryInterface
     */
    private $websiteConfigRepository;

    /**
     * @var ImageService
     */
    private $imageService;
    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var DealerLocationRepositoryInterface
     */
    private $dealerLocationRepository;
    /**
     * @var DealerLocationMileageFeeRepositoryInterface
     */
    private $dealerLocationMileageFeeRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var LoggerServiceInterface
     */
    private $logService;

    /**
     * @var ImageServiceInterface
     */
    private $imageTableService;

    /**
     * @var ResponseCacheKeyInterface
     */
    private $responseCacheKey;

    /**
     * @var GeoLocationServiceInterface
     */
    private $geoLocationService;

    /**
     * @var InventoryResponseCacheInterface
     */
    private $responseCache;

    /**
     * InventoryService constructor.
     * @param InventoryRepositoryInterface $inventoryRepository
     * @param ImageRepositoryInterface $imageRepository
     * @param FileRepositoryInterface $fileRepository
     * @param BillRepositoryInterface $billRepository
     * @param QuickbookApprovalRepositoryInterface $quickbookApprovalRepository
     * @param WebsiteConfigRepositoryInterface $websiteConfigRepository
     * @param ImageService $imageService
     * @param FileService $fileService
     * @param DealerLocationRepositoryInterface $dealerLocationRepository
     * @param DealerLocationMileageFeeRepositoryInterface $dealerLocationMileageFeeRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ImageServiceInterface $imageTableService
     * @param ResponseCacheKeyInterface $responseCacheKey
     * @param GeoLocationServiceInterface $geoLocationService
     * @param InventoryResponseCacheInterface $responseCache
     * @param LoggerServiceInterface|null $logService
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(
        InventoryRepositoryInterface $inventoryRepository,
        ImageRepositoryInterface $imageRepository,
        FileRepositoryInterface $fileRepository,
        BillRepositoryInterface $billRepository,
        QuickbookApprovalRepositoryInterface $quickbookApprovalRepository,
        WebsiteConfigRepositoryInterface $websiteConfigRepository,
        ImageService $imageService,
        FileService $fileService,
        DealerLocationRepositoryInterface $dealerLocationRepository,
        DealerLocationMileageFeeRepositoryInterface $dealerLocationMileageFeeRepository,
        CategoryRepositoryInterface $categoryRepository,
        ImageServiceInterface $imageTableService,
        ResponseCacheKeyInterface $responseCacheKey,
        GeoLocationServiceInterface $geoLocationService,
        InventoryResponseCacheInterface $responseCache,
        ?LoggerServiceInterface $logService = null
    ) {
        $this->inventoryRepository = $inventoryRepository;
        $this->imageRepository = $imageRepository;
        $this->fileRepository = $fileRepository;
        $this->billRepository = $billRepository;
        $this->quickbookApprovalRepository = $quickbookApprovalRepository;
        $this->websiteConfigRepository = $websiteConfigRepository;
        $this->dealerLocationRepository = $dealerLocationRepository;
        $this->dealerLocationMileageFeeRepository = $dealerLocationMileageFeeRepository;
        $this->imageService = $imageService;
        $this->fileService = $fileService;
        $this->categoryRepository = $categoryRepository;
        $this->imageTableService = $imageTableService;
        $this->responseCacheKey = $responseCacheKey;
        $this->geoLocationService = $geoLocationService;
        $this->responseCache = $responseCache;

        $this->logService = $logService ?? app()->make(LoggerServiceInterface::class);
    }

    /**
     * @param array $params
     * @return Inventory
     *
     * @throws InventoryException
     */
    public function create(array $params): Inventory
    {
        try {
            $inventory = Inventory::withoutCacheInvalidationAndSearchSyncing(function () use ($params) {
                $this->inventoryRepository->beginTransaction();

                $newImages = $params['new_images'] ?? [];
                $newFiles = $params['new_files'] ?? [];
                $hiddenFiles = $params['hidden_files'] ?? [];
                $clappsDefaultImage = $params['clapps']['default-image']['url'] ?? '';

                $addBill = $params['add_bill'] ?? false;

                if (!empty($params['dealer_location_id'])) {
                    $location = $this->dealerLocationRepository->get(['dealer_location_id' => $params['dealer_location_id']]);

                    if ($location->postalcode) {
                        $params['geolocation'] = $this->geoLocationService->geoPointFromZipCode($location->postalcode);
                    }
                }

                if (!empty($newImages)) {
                    $params['new_images'] = $this->uploadImages($params, 'new_images');
                }

                $newFiles = $params['new_files'] = array_merge($newFiles, $hiddenFiles);
                unset($params['hidden_files']);

                if (!empty($newFiles)) {
                    $params['new_files'] = $this->uploadFiles($params, 'new_files');
                }

                if (!empty($clappsDefaultImage)) {
                    $clappImage = $this->imageService->upload($clappsDefaultImage, $params['title'],
                        $params['dealer_id']);
                    $params['clapps']['default-image'] = $clappImage['path'];
                }

                if (!empty($params['description'])) {
                    $params['description_html'] = $this->convertMarkdown($params['description']);
                }

                $inventory = $this->inventoryRepository->create($params);

                if (!$inventory instanceof Inventory) {
                    Log::error('Item hasn\'t been created.', ['params' => $params]);
                    $this->inventoryRepository->rollbackTransaction();

                    throw new InventoryException('Inventory item create error');
                }

                if ($addBill) {
                    $this->addBill($params, $inventory);
                }

                $this->inventoryRepository->commitTransaction();

                // Generate Overlay Inventory Images if necessary
                if (!empty($newImages)) {
                    $this->dispatch((new GenerateOverlayImageJob($inventory->inventory_id))->onQueue('overlay-images'));
                }

                Log::info('Item has been successfully created', ['inventoryId' => $inventory->inventory_id]);

                return $inventory;
            });

            $this->tryToIndexAndInvalidateInventory($inventory);
        } catch (\Exception $e) {
            Log::error('Item create error. Message - ' . $e->getMessage(), $e->getTrace());
            $this->inventoryRepository->rollbackTransaction();

            throw new InventoryException('Item create error. Message - ' . $e->getMessage());
        }

        return $inventory;
    }

    /**
     * @param array $params
     * @return Inventory
     *
     * @throws InventoryException
     */
    public function update(array $params): Inventory
    {
        try {
            $inventory = Inventory::withoutCacheInvalidationAndSearchSyncing(function () use ($params) {
                $this->inventoryRepository->beginTransaction();

                $newImages = $params['new_images'] ?? [];
                $existingImages = $params['existing_images'] ?? [];
                $newFiles = $params['new_files'] ?? [];
                $hiddenFiles = $params['hidden_files'] ?? [];
                $clappsDefaultImage = $params['clapps']['default-image']['url'] ?? '';

                $options = [
                    'updateAttributes' => $params['update_attributes'] ?? false,
                    'updateFeatures' => $params['update_features'] ?? false,
                    'updateClapps' => $params['update_clapps'] ?? false,
                ];

                $source = $params['source'] ?? '';
                $addBill = $params['add_bill'] ?? false;

                if (!empty($newImages)) {
                    $params['new_images'] = $this->uploadImages($params, 'new_images');
                }

                if (!empty($params['status']) && $params['status'] == Inventory::STATUS_SOLD) {
                    $params['sold_at'] = Carbon::now()->format('Y-m-d H:i:s');
                } else {
                    $params['sold_at'] = null;
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

                if (!empty($params['description'])) {
                    $params['description_html'] = $this->convertMarkdown($params['description']);
                }

                if (!empty($params['is_archived']) && $params['is_archived'] == 1) {
                    $params['archived_at'] = Carbon::now()->format('Y-m-d H:i:s');
                }

                if (!empty($params['dealer_location_id'])) {
                    $location = $this->dealerLocationRepository->get(['dealer_location_id' => $params['dealer_location_id']]);

                    if ($location->postalcode) {
                        $params['geolocation'] = $this->geoLocationService->geoPointFromZipCode($location->postalcode);
                    }
                }

                $inventory = $this->inventoryRepository->update($params, $options);
                $changes = $inventory->getChanges();

                if (!$inventory instanceof Inventory) {
                    Log::error('Item hasn\'t been updated.', ['params' => $params]);
                    $this->inventoryRepository->rollbackTransaction();

                    throw new InventoryException('Inventory item update error');
                }

                if ($addBill) {
                    $this->addBill($params, $inventory);
                }

                if ($source === self::SOURCE_DASHBOARD) {
                    $this->inventoryRepository->update([
                        'inventory_id' => $params['inventory_id'],
                        'changed_fields_in_dashboard' => $this->getChangedFields($inventory, $params)
                    ]);
                }

                $this->inventoryRepository->commitTransaction();

                // Generate Overlay Inventory Images if necessary
                if (!empty($newImages) || !empty($existingImages) ||
                    (!empty($changes) && isset($changes['overlay_enabled']))) {
                    Log::channel('inventory-overlays')
                       ->info('Queue regenerating overlays just for Inventory ID #' . $inventory->inventory_id);
                    $this->dispatch((new GenerateOverlayImageJob($inventory->inventory_id))->onQueue('overlay-images'));
                }

                Log::info('Item has been successfully updated', ['inventoryId' => $inventory->inventory_id]);

                return $inventory;
            });

            $this->tryToIndexAndInvalidateInventory($inventory);
        } catch (\Exception $e) {
            Log::error('Item update error. Message - ' . $e->getMessage(), $e->getTrace());
            $this->inventoryRepository->rollbackTransaction();

            throw new InventoryException('Inventory item update error');
        }

        return $inventory;
    }

    /**
     * @param array $params
     * @return bool
     * @throws InventoryException
     */
    public function massUpdate(array $params): bool
    {
        try {
            $this->inventoryRepository->beginTransaction();

            // if $params['dealer_id'] is not present it will throw an exception
            Inventory::withoutCacheInvalidationAndSearchSyncing(function () use ($params){
                $this->inventoryRepository->massUpdate($params);
            });

            $this->inventoryRepository->commitTransaction();

            $this->invalidateCacheAndReindexByDealerIds([$params['dealer_id']]);
        } catch (\Exception $e) {
            Log::error('Inventory mass update error. Message - ' . $e->getMessage(), $e->getTrace());
            $this->inventoryRepository->rollbackTransaction();

            throw new InventoryException('Inventory mass update error');
        }

        return true;
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
                // $this->dispatch((new DeleteS3FilesJob($imagesFilenames))->onQueue('files'));
            }

            if (isset($filesFilenames) && $result) {
                // $this->dispatch((new DeleteS3FilesJob($filesFilenames))->onQueue('files'));
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
     * @return array
     */
    public function archiveSoldItems(): array
    {
        $result = [];

        /** @var Collection $configs */
        $configs = $this->websiteConfigRepository->getAll([
            'key' => WebsiteConfig::DURATION_BEFORE_AUTO_ARCHIVING_KEY,
            'value_gt' => 0,
            'with' => ['website'],
        ]);

        $dealerIds = $configs->mapWithKeys(function ($config) {
            return [$config->website->dealer_id => $config->value];
        });

        foreach ($dealerIds as $dealerId => $hours) {
            /** @var Inventory[] $inventories */
            $inventories = $this->inventoryRepository->getAll([
                'dealer_id' => $dealerId,
                'status' => Inventory::STATUS_SOLD,
                'sold_at_lt' => Carbon::now()->subHours($hours),
                'is_archived' => 0,
                'integration_item_hash' => 'not_null',
            ]);

            $result = Inventory::withoutCacheInvalidationAndSearchSyncing(function () use ($inventories): array {
                $result = [];

                foreach ($inventories as $inventory) {
                    $this->inventoryRepository->update([
                        'inventory_id' => $inventory->inventory_id,
                        'is_archived' => 1,
                        'archived_at' => Carbon::now()
                    ]);

                    $result[] = $inventory->inventory_id;
                }

                return $result;
            });

            $this->invalidateCacheAndReindexByDealerIds([$dealerId]);
        }

        return $result;
    }

    /**
     * @param array $params
     * @param string $imagesKey
     * @return array
     *
     * @throws \App\Exceptions\File\FileUploadException
     * @throws \App\Exceptions\File\ImageUploadException
     */
    protected function uploadImages(array $params, string $imagesKey): array
    {
        $images = $params[$imagesKey];

        $isOverlayEnabled = isset($params['overlay_enabled']) && in_array($params['overlay_enabled'], Inventory::OVERLAY_CODES);
        $overlayEnabledParams = [
            'skipNotExisting' => true,
            'visibility' => config('filesystems.disks.s3.visibility')
        ];

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
            $fileDto = $this->imageService->upload($image['url'], $params['title'], $params['dealer_id'], null, $overlayEnabledParams);
            if (empty($fileDto)) {
                continue;
            }

            $image['filename'] = $fileDto->getPath();
            $image['filename_noverlay'] = '';
            $image['hash'] = $fileDto->getHash();
        }

        foreach ($withOverlay as &$image) {
            $noOverlayFileDto = $this->imageService->upload($image['url'], $params['title'], $params['dealer_id'], null, $overlayEnabledParams);
            $overlayFileDto = $this->imageService->upload($image['url'], $params['title'], $params['dealer_id'], null, $overlayEnabledParams);
            if (empty($noOverlayFileDto) || empty($overlayFileDto)) {
                continue;
            }

            $image['filename'] = $overlayFileDto->getPath();
            $image['filename_noverlay'] = $noOverlayFileDto->getPath();
            $image['hash'] = $overlayFileDto->getHash();
        }

        return array_merge($withOverlay, $withoutOverlay);
    }

    /**
     * Apply Overlays to Inventory Images
     *
     * @param int $inventoryId
     * @return void
     */
    public function generateOverlays(int $inventoryId)
    {
        $inventoryImages = $this->inventoryRepository->getInventoryImages($inventoryId);

        if ($inventoryImages->count() === 0) return;

        $overlayParams = $this->inventoryRepository->getOverlayParams($inventoryId);

        Log::channel('inventory-overlays')->info('Adding Overlays on Inventory Images', $overlayParams);

        $overlayEnabled = $overlayParams['overlay_enabled'];

        foreach ($inventoryImages as $inventoryImage) {

            $imageObj = $inventoryImage->image;

            // Add Overlays if enabled
            if ($overlayEnabled == Inventory::OVERLAY_ENABLED_ALL
                || (
                    $overlayEnabled == Inventory::OVERLAY_ENABLED_PRIMARY
                    && ($inventoryImage->position == 1 || $inventoryImage->is_default == 1)
                    )
                ) {

                // apply overlays
                $originalFilename = !empty($imageObj->filename_noverlay) ? $imageObj->filename_noverlay : $imageObj->filename;
                $localNewImagePath = $this->imageService->addOverlays($this->getS3BaseUrl() . $originalFilename, $overlayParams);

                if (!empty($localNewImagePath)) {
                    // upload overlay image
                    $randomFilename = md5($localNewImagePath);
                    $newFilename = $this->imageService->uploadToS3($localNewImagePath, $randomFilename, $overlayParams['dealer_id']);
                    unlink($localNewImagePath);

                    // update image to database
                    $this->imageTableService->saveOverlay($imageObj, $newFilename);
                } else {

                    Log::channel('inventory-overlays')
                        ->error('Failed Adding Overlays, Invalid OverlayParams',
                            array_merge($overlayParams, ['image_id' => $imageObj->image_id]));
                }

            // otherwise Reset Overlay
            } else {

                $this->imageTableService->resetOverlay($imageObj);
            }
        }
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
            $fileDto = $this->fileService->upload($file['url'], $file['title'], $params['dealer_id'], $params['inventory_id'] ?? null);

            $file['path'] = $fileDto->getPath();
            $file['type'] = $fileDto->getMimeType();
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

                $this->quickbookApprovalRepository->deleteByTbPrimaryId($bill->id, 'qb_bills', $inventory->dealer_id);

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
                'vendor_id' => $fpVendor,
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

    /**
     * @param Inventory $inventory
     * @param array $params
     * @return array
     */
    private function getChangedFields(Inventory $inventory, array $params): array
    {
        $changedFields = array_values(array_unique(array_merge(
            $inventory->changed_fields_in_dashboard ?? [], $params['changed_fields_in_dashboard'] ?? []
        )));

        foreach ($params as $field => $param) {
            if (strpos($field, 'unlock_') === 0) {
                $changedFields = array_diff($changedFields, [str_replace('unlock_', '', $field)]);
            }
        }

        foreach (self::CHANGED_FIELDS_IN_DASHBOARD_UNLOCK_MAPPING as $unlockKey => $fields) {
            if (!isset($params[$unlockKey])) {
                continue;
            }

            foreach ($fields as $field) {
                $changedFields = array_diff($changedFields, [$field]);
            }
        }

        return array_values($changedFields);
    }

    public function deliveryPrice(int $inventoryId, string $toZip=null): float
    {
        $inventory = $this->inventoryRepository->get(['id' => $inventoryId]);
        $dealerLocation = $inventory->dealerLocation;
        $inventoryCategory = $this->categoryRepository->get(['legacy_category' => $inventory->category]);

        $mileageFee = $this->dealerLocationMileageFeeRepository->get([
            'dealer_location_id' => $dealerLocation->getKey(),
            'inventory_category_id' => $inventoryCategory->getKey()
        ]);
        $feePerMile = $mileageFee->fee_per_mile;
        $fromLat = $dealerLocation->latitude;
        $fromLng = $dealerLocation->longitude;

        if($toZip !== null) {
            $geolocation = $this->geoLocationService->geoPointFromZipCode($toZip);
            if($geolocation){
                $fromLat = $geolocation->getLat();
                $fromLng = $geolocation->getLng();
            }
        }

        $toLat  = $inventory->latitude;
        $toLong = $inventory->longitude;

        if (empty($toLat) || empty($toLong)) {
            $toLat  = $dealerLocation->latitude;
            $toLong = $dealerLocation->longitude;
        }

        $distance = $this->calculateDistanceBetweenTwoPoints($fromLat, $fromLng, $toLat, $toLong, 'ML');

        return $feePerMile * $distance;
    }

    private function calculateDistanceBetweenTwoPoints($latitudeOne='', $longitudeOne='', $latitudeTwo='', $longitudeTwo='', $distanceUnit ='', $round=false, $decimalPoints='')
    {
        if (empty($decimalPoints))
        {
            $decimalPoints = '3';
        }
        if (empty($distanceUnit)) {
            $distanceUnit = 'KM';
        }
        $distanceUnit = strtolower($distanceUnit);
        $pointDifference = $longitudeOne - $longitudeTwo;
        $toSin = (sin(deg2rad($latitudeOne)) * sin(deg2rad($latitudeTwo))) + (cos(deg2rad($latitudeOne)) * cos(deg2rad($latitudeTwo)) * cos(deg2rad($pointDifference)));
        $toAcos = acos($toSin);
        $toRad2Deg = rad2deg($toAcos);

        $toMiles  =  $toRad2Deg * 60 * 1.1515;
        $toKilometers = $toMiles * 1.609344;
        $toNauticalMiles = $toMiles * 0.8684;
        $toMeters = $toKilometers * 1000;
        $toFeets = $toMiles * 5280;
        $toYards = $toFeets / 3;


        switch (strtoupper($distanceUnit))
        {
            case 'ML'://miles
                $toMiles  = ($round == true ? round($toMiles) : round($toMiles, $decimalPoints));
                return $toMiles;
            case 'KM'://Kilometers
                $toKilometers  = ($round == true ? round($toKilometers) : round($toKilometers, $decimalPoints));
                return $toKilometers;
            case 'MT'://Meters
                $toMeters  = ($round == true ? round($toMeters) : round($toMeters, $decimalPoints));
                return $toMeters;
            case 'FT'://feets
                $toFeets  = ($round == true ? round($toFeets) : round($toFeets, $decimalPoints));
                return $toFeets;
            case 'YD'://yards
                $toYards  = ($round == true ? round($toYards) : round($toYards, $decimalPoints));
                return $toYards;
            case 'NM'://Nautical miles
                $toNauticalMiles  = ($round == true ? round($toNauticalMiles) : round($toNauticalMiles, $decimalPoints));
                return $toNauticalMiles;
        }

    }

    /**
     * @param int $inventoryId
     * @param array $params
     * @return InventoryImage
     * @throws FileUploadException
     * @throws ImageUploadException
     */
    public function createImage(int $inventoryId, array $params): InventoryImage
    {
        /** @var Inventory $inventory */
        $inventory = $this->inventoryRepository->get(['id' => $inventoryId]);

        $images = $this->uploadImages([
            'title' => $inventory->title,
            'dealer_id' => $inventory->dealer_id,
            'images' => [$params]
        ], 'images');

        $inventoryImages = $this->inventoryRepository->createInventoryImages($inventory, $images);

        return array_pop($inventoryImages);
    }

    /**
     * @param int $inventoryId
     * @param array $params
     * @return File
     * @throws FileUploadException
     */
    public function createFile(int $inventoryId, array $params): File
    {
        /** @var Inventory $inventory */
        $inventory = $this->inventoryRepository->get(['id' => $inventoryId]);

        $files = $this->uploadFiles([
            'dealer_id' => $inventory->dealer_id,
            'inventory_id' => $inventory->inventory_id,
            'files' => [$params]
        ], 'files');

        $inventoryFiles = $this->inventoryRepository->createInventoryFiles($inventory, $files);

        return $inventoryFiles[0]->file;
    }

    /**
     * Deletes the inventory images from the DB and the filesystem
     *
     * @param int $inventoryId
     * @param int[] $imageIds
     * @return bool
     * @throws \RuntimeException when the images could not be deleted
     */
    public function imageBulkDelete(int $inventoryId, array $imageIds = null): bool
    {
        try {
            $this->inventoryRepository->beginTransaction();

            if (empty($imageIds)) {
                $imageIds = $this->imageRepository
                    ->getAll(['inventory_id' => $inventoryId,])
                    ->pluck('image_id')
                    ->toArray();
            }

            $imagesFilenames = $this->imageRepository
                ->getAll([
                    'inventory_id' => $inventoryId,
                    ImageRepositoryInterface::CONDITION_AND_WHERE_IN => ['inventory_image.image_id' => $imageIds]
                ])
                ->pluck('filename')
                ->toArray();

            $this->imageRepository->delete([
                ImageRepositoryInterface::CONDITION_AND_WHERE_IN => ['image_id' => $imageIds]
            ]);

            // $this->dispatch((new DeleteS3FilesJob($imagesFilenames))->onQueue('files'));

            $this->inventoryRepository->commitTransaction();

            $this->logService->info('Images have been successfully deleted', ['image_ids' => $imageIds]);
        } catch (\Exception $e) {
            $this->inventoryRepository->rollbackTransaction();

            $message = sprintf('Images deletion have failed: %s', $e->getMessage());

            $this->logService->error($message, ['image_ids' => $imageIds]);

            throw new \RuntimeException($message);
        }

        return true;
    }

    /**
     * @param int $inventoryId
     * @return bool
     */
    public function fileBulkDelete(int $inventoryId): bool
    {
        try {
            $fileIds = $this->fileRepository
                ->getAllByInventoryId($inventoryId)
                ->pluck('file_id')
                ->toArray();

            $this->fileRepository->delete([
                FileRepositoryInterface::CONDITION_AND_WHERE_IN => ['id' => $fileIds]
            ]);

            $this->logService->info('Files have been successfully deleted', ['file_ids' => $fileIds]);
        } catch (\Exception $e) {
            $message = sprintf('Files deletion have failed: %s', $e->getMessage());

            $this->logService->error($message, ['file_ids' => $fileIds ?? []]);

            throw new \RuntimeException($message);
        }

        return true;
    }

    public function getInventoriesTitle(array $params): array
    {
        $dealerInventories = $this->inventoryRepository->getTitles($params['dealer_id']);

        if (!empty($params['customer_id'])) {
            $customerInventoryRepo = resolve(DmsCustomerInventoryRepository::class);
            $customerInventories = $customerInventoryRepo->getTitles($params['customer_id']);
        }

        return [
            $this->processTitles($customerInventories ?? [], self::OPTION_GROUP_TEXT_CUSTOMER_OWNED),
            $this->processTitles($dealerInventories, self::OPTION_GROUP_TEXT_DEALER_OWNED),
        ];
    }

    private function processTitles($data, $text): array
    {
        $resource = new FractalResourceCollection(
            $data,
            new InventoryTitleAndVinTransformer,
            self::RESOURCE_KEY
        );

        return $this->processTitleGroups($resource, $text);
    }

    private function processTitleGroups($data, $text): array
    {
        $resultantArray = [];
        if (!empty($data)) {
            $fractalManager = new FractalManager();
            $fractalManager->setSerializer(new NoDataArraySerializer());
            $resultantArray = $fractalManager->createData($data)->toArray();
        }

        return array_merge($resultantArray, [
            'text' => $text,
        ]);
    }

    /**
     * Exports an inventory and returns the url to the export
     *
     * @param int $inventoryId
     * @param string $format
     * @return string
     */
    public function export(int $inventoryId, string $format): string
    {
        $instance = [
            self::PDF_EXPORT => PdfExporter::class
        ][$format];
        return (new $instance)->export($this->inventoryRepository->get(['id' => $inventoryId]));
    }

    public function convertMarkdown($input): string
    {
        $input = str_replace('\n', PHP_EOL, $input);
        $input = str_replace('\\' . PHP_EOL, PHP_EOL . PHP_EOL, $input); // to fix CDW-824 problems
        $input = str_replace('\\' . PHP_EOL . 'n', PHP_EOL . PHP_EOL . PHP_EOL, $input);

        $input = str_replace('\\\\', '', $input);
        $input = str_replace('\\,', ',', $input);
//        $input = str_replace('****', '', $input);
//        $input = str_replace('__', '', $input);

        $input = str_replace('<BR>', '\n', $input);
        $input = str_replace('<BR/>', '\n', $input);
        $input = str_replace('<Br/>', '\n', $input);
        $input = str_replace('<br/>', '\n', $input);
        $input = str_replace('<bR/>', '\n', $input);
        $input = str_replace('<bR>', '\n', $input);
        //$input = preg_replace('/<(?!br\s*\/?)[^<>]+>/', '\n', $input);
        $input = nl2br($input, false);

        // Try/Catch Errors
        $converted = '';
        $exception = '';
        try {
            // Initialize Markdown Converter
            $converter = new \Parsedown(); // This parser is 10x faster than the CommonMarkConverter
            $converter->setBreaksEnabled(false);
            $converter->setSafeMode(false);
            $converted = $converter->text($input);
        } catch(\Exception $e) {
            $exception = $e->getMessage();
        }

        // Convert Markdown to HTML
        //$description = preg_replace('/\\\\/', '<br>', $converted);

        // to fix CDW-824 problems
        //$description = nl2br($converted);

        // taken from previous CDW-824 solution
        $description = str_replace('<code>', '', $converted);
        $description = str_replace('</code>', '', $description);
        $description = str_replace('<pre>', '', $description);
        $description = str_replace('</pre>', '', $description);

        $description = $this->fixNonAsciiChars($description);

        $description = str_replace("\r\n","",$description);
        $description = trim(preg_replace('/\s\s+/', ' ', $description));

        $description = str_replace(PHP_EOL, '', $description);

        // Return
        return $description;
    }

    /**
     * @param string $description
     * @return array|string|string[]|null
     */
    private function fixNonAsciiChars(string $description)
    {

        //$description = preg_replace('/(\\?\*){2,}/', '**', $description);
        $description = preg_replace('/(\\?_)+/', '_', $description);

        // Fix 0xa0 or nbsp
        $description = preg_replace('/\xA0/', ' ', $description);
        $description = preg_replace('/\xBE/', '3/4', $description);
        $description = preg_replace('/\xBC/', '1/4', $description);
        $description = preg_replace('/\xBD/', '1/2', $description);

        $description = preg_replace('/\x91/', "'", $description);
        $description = preg_replace('/\x92/', "'", $description);
        $description = preg_replace('/\xB4/', "'", $description);
        $description = preg_replace('/\x27/', "'", $description);

        //$description = preg_replace('/\x93/', '"', $description);
        //$description = preg_replace('/\x94/', '"', $description);

        $description = preg_replace('/”/', '"', $description);
        $description = preg_replace('/’/', "'", $description);

        $description = preg_replace('/©/', "Copyright", $description);
        $description = preg_replace('/®/', "Registered", $description);

        //$description = preg_replace('/[[:^print:]]/', ' ', $description);

        preg_match('/<blockquote>(.*?)<\/blockquote>/s', $description, $match);
        if (!empty($match[0])) {
            $new_ul = strip_tags($match[0], '<blockquote><br><ul><ol><li><a><b><strong>');
            $description = str_replace($match[0], $new_ul, $description);
        }


        preg_match('/<ul>(.*?)<\/ul>/s', $description, $match);
        if (!empty($match[0])) {
            $new_ul = strip_tags($match[0], '<ul><li><a><b><strong>');
            $description = str_replace($match[0], $new_ul, $description);
        }

        // Only accepts necessary tags
        preg_match('/<ol>(.*?)<\/ol>/s', $description, $match);
        if (!empty($match[0])) {
            $new_ol = strip_tags($match[0], '<ol><li><a><b><strong>');
            $description = str_replace($match[0], $new_ol, $description);
        }

        $description = str_replace('\n', '', $description);

        return $description;
    }

    /**
     * Reindex the inventory by dealer ids, then it will invalidate cache by dealer ids
     *
     * @param  int[]  $dealerIds
     * @return void
     */
    public function invalidateCacheAndReindexByDealerIds(array $dealerIds): void
    {
        $this->dispatch(new ReIndexInventoriesByDealersJob($dealerIds));
    }

    /**
     * Reindex the inventory by dealer location id, then it will invalidate cache by dealer id
     *
     * @param  DealerLocation  $dealerLocation
     * @return void
     */
    public function invalidateCacheAndReindexByDealerLocation(DealerLocation $dealerLocation): void
    {
        $this->dispatch(new ReIndexInventoriesByDealerLocationJob($dealerLocation->dealer_location_id));
    }

    /**
     * - Will try to index a given inventory only when ES indexation is enabled
     * - Will try invalidate inventory cache for a given inventory only when cache invalidation is enabled
     *
     * @param  Inventory  $inventory
     * @return void
     */
    public function tryToIndexAndInvalidateInventory(Inventory $inventory): void
    {
        if (Inventory::isSearchSyncingEnabled()) {
            $inventory->searchable();
        }

        if (Inventory::isCacheInvalidationEnabled()) {
            $keyPatterns = [$this->responseCacheKey->deleteByDealer($inventory->dealer_id)];

            if (!$inventory->wasRecentlyCreated) {
                $keyPatterns[] = $this->responseCacheKey->deleteSingle($inventory->inventory_id, $inventory->dealer_id);
            }

            $this->responseCache->forget($keyPatterns);
        }
    }
}
