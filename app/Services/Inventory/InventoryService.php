<?php

namespace App\Services\Inventory;

use App\Helpers\ConvertHelper;
use App\Helpers\SanitizeHelper;
use App\Jobs\Files\DeleteS3FilesJob;
use App\Models\Inventory\Inventory;
use App\Repositories\Inventory\AttributeRepositoryInterface;
use App\Repositories\Inventory\FileRepositoryInterface;
use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Repository;
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

    private const FEET_SECOND_FORMAT = '%s_second';
    private const INCHES_SECOND_FORMAT = '%s_inches_second';

    private const FEET_INCHES_FIELDS = [
        "width",
        "length",
        "height",
    ];

    private const VIDEO_EMBED_FIELDS = [
        'video_embed_code'
    ];

    private const FEET_DECIMAL_FIELDS = [
        "width",
        "length",
        "height",
        "shortwall_length",
    ];

    private const POUND_DECIMAL_FIELDS = [
        'weight',
        'gvwr',
        'axle_capacity',
    ];

    private const FIELDS_MAPPING = [
        'dealer_identifier' => 'dealer_id',
        'entity_type' => 'entity_type_id',
        'dealer_location_identifier' => 'dealer_location_id',
        'external_color' => 'color',
        'exterior_color' => 'color',
    ];

    private const SANITIZE_UTF8_FIELDS = [
        'description'
    ];

    private const PRICE_FIELDS = [
        "msrp",
        "price",
        "sales_price",
        "website_price",
        "hidden_price",
    ];

    private const DEPENDED_FIELDS = [
        'use_website_price' => 'website_price',
    ];

    private const NOT_NULL_FIELDS = [
        'hidden_price',
        'chosen_overlay',
        'pac_type',
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
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var ConvertHelper
     */
    private $convertHelper;
    /**
     * @var SanitizeHelper
     */
    private $sanitizeHelper;

    /**
     * InventoryService constructor.
     * @param InventoryRepositoryInterface $inventoryRepository
     * @param ImageRepositoryInterface $imageRepository
     * @param FileRepositoryInterface $fileRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ConvertHelper $convertHelper
     * @param SanitizeHelper $sanitizeHelper
     */
    public function __construct(
        InventoryRepositoryInterface $inventoryRepository,
        ImageRepositoryInterface $imageRepository,
        FileRepositoryInterface $fileRepository,
        AttributeRepositoryInterface $attributeRepository,
        ConvertHelper $convertHelper,
        SanitizeHelper $sanitizeHelper
    ) {
        $this->inventoryRepository = $inventoryRepository;
        $this->imageRepository = $imageRepository;
        $this->fileRepository = $fileRepository;
        $this->attributeRepository = $attributeRepository;

        $this->sanitizeHelper = $sanitizeHelper;
        $this->convertHelper = $convertHelper;
    }

    /**
     * @param array $params
     * @return bool
     */
    public function create(array $params)
    {
        //try {
            $createParams = $this->prepareParams($params);

            $this->inventoryRepository->beginTransaction();

            $inventory = $this->inventoryRepository->create($createParams);

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
/*        } catch (\Exception $e) {
            Log::error('Item create error.', $e->getTrace());
            $this->inventoryRepository->rollbackTransaction();

            return false;
        }*/

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
    private function prepareParams(array $params): array
    {
        $convertHelper = $this->convertHelper;
        $sanitizeHelper = $this->sanitizeHelper;

        $defaultAttributes = $this->attributeRepository
            ->getAllByEntityTypeId($params['entity_type_id'])
            ->pluck('code', 'attribute_id')
            ->toArray();

        $createParams = $params;
        $attributes = [];
        $features = [];

        foreach($createParams as $key => $value) {
            if(is_array($value)) {
                $createParams = array_merge($value, $createParams);
            }
        }

        $createParams = array_filter($createParams,
            function ($paramsKey) {
                return !is_numeric($paramsKey);
            },
            ARRAY_FILTER_USE_KEY
        );

        foreach (self::FIELDS_MAPPING as $paramsField => $modelField) {
            if (!isset($createParams[$modelField]) && isset($createParams[$paramsField])) {
                $createParams[$modelField] = $createParams[$paramsField];
            }
        }

        foreach (self::FEET_INCHES_FIELDS as $feetInchesField) {
            $feetSecond = sprintf(self::FEET_SECOND_FORMAT, $feetInchesField);
            $inchesSecond = sprintf(self::INCHES_SECOND_FORMAT, $feetInchesField);

            $createParams[$feetInchesField] = $convertHelper->feetInchesToFeet((float)$feetSecond, (float)$inchesSecond);
        }

        foreach (self::VIDEO_EMBED_FIELDS as $embedField) {
            if(!empty($params[$embedField]) && is_array($params[$embedField])) {
                $createParams[$embedField] = $sanitizeHelper->splitVideoEmbedCode($createParams[$embedField]);
            }
        }

        array_walk($createParams, function ($item) use ($sanitizeHelper) {
            return is_string($item) ? $sanitizeHelper->removeTypographicCharacters($item) : $item;
        });

        foreach (self::FEET_DECIMAL_FIELDS as $decimalField) {
            if (isset($createParams[$decimalField])) {
                $createParams[$decimalField] = $convertHelper->toFeetDecimal($createParams[$decimalField], 2);
            }
        }

        foreach (self::POUND_DECIMAL_FIELDS as $decimalField) {
            if (isset($createParams[$decimalField])) {
                $createParams[$decimalField] = $convertHelper->toPoundsDecimal($createParams[$decimalField], 2);
            }
        }

        foreach (self::SANITIZE_UTF8_FIELDS as $sanitizeField) {
            if (isset($createParams[$sanitizeField])) {
                $createParams[$sanitizeField] = $sanitizeHelper->stripMultipleWhitespace($sanitizeHelper->utf8($createParams[$sanitizeField]));
            }
        }

        foreach (self::PRICE_FIELDS as $priceField) {
            if (isset($createParams[$priceField])) {
                $createParams[$priceField] = $convertHelper->toPrice($createParams[$priceField]);
            }
        }

        foreach (self::DEPENDED_FIELDS as $masterField => $dependedField) {
            if (!isset($createParams[$masterField]) || $createParams[$masterField] != 1) {
                $createParams[$dependedField] = null;
            }
        }

        foreach (self::NOT_NULL_FIELDS as $notNullField) {
            if (array_key_exists($notNullField, $createParams) && is_null($createParams[$notNullField])) {
                unset($createParams[$notNullField]);
            }
        }

        foreach ($createParams as $createParamKey => $createParamValue) {
            if (in_array($createParamKey, $defaultAttributes) && !empty($createParamValue)) {
                if (!isset($createParams['ignore_attributes']) || $createParams['ignore_attributes'] != 1) {
                    $attributeId = array_search($createParamKey, $defaultAttributes);
                    $attributes[] = [
                        'attribute_id' => $attributeId,
                        'value' => $createParamValue,
                    ];
                }

                unset($createParams[$createParamKey]);

            } elseif (substr($createParamKey, 0, 8) == 'feature_' && !empty($createParamValue)) {
                list(, $featureId) = explode('_', $createParamKey);

                foreach($createParamValue as $value) {
                    if (empty($value)) {
                        continue;
                    }

                    $features[] = [
                        'feature_list_id' => $featureId,
                        'value' => $value,
                    ];
                }

                unset($createParams[$createParamKey]);
            }
        }

        $createParams['attributes'] = $attributes;
        $createParams['features'] = $features;

        return $createParams;
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
}
