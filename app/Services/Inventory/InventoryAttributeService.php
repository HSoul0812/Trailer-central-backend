<?php

namespace App\Services\Inventory;

use App\Exceptions\Inventory\InventoryException;
use App\Jobs\Files\DeleteS3FilesJob;
use App\Models\CRM\Dms\Quickbooks\Bill;
use App\Models\Inventory\Inventory;
use App\Models\Website\Config\WebsiteConfig;
use App\Repositories\Dms\Quickbooks\BillRepositoryInterface;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepositoryInterface;
use App\Repositories\Inventory\AttributeValueRepositoryInterface;
use App\Repositories\Inventory\CategoryRepositoryInterface;
use App\Repositories\Inventory\FileRepositoryInterface;
use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Repository;
use App\Repositories\User\DealerLocationMileageFeeRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Repositories\User\GeoLocationRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Services\File\FileService;
use App\Services\File\ImageService;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class InventoryAttributeService
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
     * @var GeoLocationRepositoryInterface
     */
    private $geolocationRepository;

    /**
     * @param InventoryRepositoryInterface $inventoryRepository
     * @param AttributeValueRepositoryInterface $attributeValueRepository
     * @param GeoLocationRepositoryInterface $geolocationRepository
     */
    public function __construct(
        InventoryRepositoryInterface $inventoryRepository,
        AttributeValueRepositoryInterface $attributeValueRepository,
        GeoLocationRepositoryInterface $geolocationRepository
    ) {
        $this->inventoryRepository = $inventoryRepository;
        $this->attributeValueRepository = $attributeValueRepository;
        // $this->geolocationRepository = $geolocationRepository;
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
            $newImages = $params['new_images'] ?? [];
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

            $newFiles = $params['new_files'] = array_merge($newFiles, $hiddenFiles);
            unset($params['hidden_files']);

            if (!empty($newFiles)) {
                $params['new_files'] = $this->uploadFiles($params, 'new_files');
            }

            if (!empty($clappsDefaultImage)) {
                $clappImage = $this->imageService->upload($clappsDefaultImage, $params['title'], $params['dealer_id']);
                $params['clapps']['default-image'] = $clappImage['path'];
            }

            $inventory = $this->inventoryRepository->update($params, $options);

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

            Log::info('Item has been successfully updated', ['inventoryId' => $inventory->inventory_id]);
        } catch (\Exception $e) {
            Log::error('Item update error. Message - ' . $e->getMessage(), $e->getTrace());
            $this->inventoryRepository->rollbackTransaction();

            throw new InventoryException('Inventory item update error');
        }

        return $inventory;
    }
}
