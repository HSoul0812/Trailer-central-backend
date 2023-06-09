<?php

namespace App\Services\Showroom;

use App\Exceptions\Showroom\ShowroomException;
use App\Helpers\ConvertHelper;
use App\Models\Showroom\Showroom;
use App\Models\Showroom\ShowroomFeature;
use App\Repositories\Inventory\AttributeRepositoryInterface;
use App\Repositories\Showroom\ShowroomFeatureRepositoryInterface;
use App\Repositories\Showroom\ShowroomFieldsMappingRepositoryInterface;
use App\Repositories\Showroom\ShowroomFileRepositoryInterface;
use App\Repositories\Showroom\ShowroomGenericMapRepositoryInterface;
use App\Repositories\Showroom\ShowroomImageRepositoryInterface;
use App\Repositories\Showroom\ShowroomRepositoryInterface;
use App\Traits\S3\S3Helper;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ShowroomService
 * @package App\Services\Showroom
 */
class ShowroomService implements ShowroomServiceInterface
{
    use S3Helper;

    private const DEFAULT_FILE_ACTIVE = 1;
    private const DEFAULT_ENTITY_TYPE = 1;

    /**
     * @var ShowroomFieldsMappingRepositoryInterface
     */
    private $showroomFieldsMappingRepository;

    /**
     * @var ShowroomRepositoryInterface
     */
    private $showroomRepository;

    /**
     * @var ShowroomGenericMapRepositoryInterface
     */
    private $showroomGenericMapRepository;

    /**
     * @var ShowroomFeatureRepositoryInterface
     */
    private $showroomFeatureRepository;

    /**
     * @var ShowroomFileRepositoryInterface
     */
    private $showroomFileRepository;

    /**
     * @var ShowroomImageRepositoryInterface
     */
    private $showroomImageRepository;

    /**
     * @var AttributeRepositoryInterface
     */
    private $inventoryAttributeRepository;

    /**
     * @var bool
     */
    private $hasStockImages = false;

    /**
     * @var ConvertHelper
     */
    private $convertHelper;

    /**
     * @param ShowroomFieldsMappingRepositoryInterface $showroomFieldsMappingRepository
     * @param ShowroomRepositoryInterface $showroomRepository
     * @param ShowroomGenericMapRepositoryInterface $showroomGenericMapRepository
     * @param ShowroomFeatureRepositoryInterface $showroomFeatureRepository
     * @param ShowroomFileRepositoryInterface $showroomFileRepository
     * @param ShowroomImageRepositoryInterface $showroomImageRepository
     * @param AttributeRepositoryInterface $inventoryAttributeRepository
     * @param ConvertHelper $convertHelper
     */
    public function __construct(
        ShowroomFieldsMappingRepositoryInterface $showroomFieldsMappingRepository,
        ShowroomRepositoryInterface $showroomRepository,
        ShowroomGenericMapRepositoryInterface $showroomGenericMapRepository,
        ShowroomFeatureRepositoryInterface $showroomFeatureRepository,
        ShowroomFileRepositoryInterface $showroomFileRepository,
        ShowroomImageRepositoryInterface $showroomImageRepository,
        AttributeRepositoryInterface $inventoryAttributeRepository,
        ConvertHelper $convertHelper
    ) {
        $this->showroomFieldsMappingRepository = $showroomFieldsMappingRepository;
        $this->showroomRepository = $showroomRepository;
        $this->showroomGenericMapRepository = $showroomGenericMapRepository;
        $this->showroomFeatureRepository = $showroomFeatureRepository;
        $this->showroomFileRepository = $showroomFileRepository;
        $this->showroomImageRepository = $showroomImageRepository;
        $this->inventoryAttributeRepository = $inventoryAttributeRepository;
        $this->convertHelper = $convertHelper;
    }

    /**
     * @param array $unit
     * @param array $additionalSearchParams
     * @param array $options
     * @return array
     */
    public function mapInventoryToFactory(array $unit, array $additionalSearchParams = [], array $options = []): array
    {
        $showroom = $this->getShowroomByUnit($unit, $additionalSearchParams);

        if ($showroom === null) {
            return $unit;
        }

        $showroomMappings = $this->showroomFieldsMappingRepository->getAll([]);

        $attributes = [];
        $rewritableFields = $options[self::REWRITABLE_FIELDS_OPTION] ?? [];

        $defaultAttributes = $this->inventoryAttributeRepository
            ->getAllByEntityTypeId($unit['entity_type_id'] ?? self::DEFAULT_ENTITY_TYPE)
            ->pluck('attribute_id', 'code')
            ->toArray();

        foreach ($showroomMappings as $showroomMapping) {
            if (empty($showroom->{$showroomMapping->map_from}) || is_object($showroom->{$showroomMapping->map_from})) {
                continue;
            }

            if ($showroomMapping->type === 'attribute') {
                $currentAttributeKey = null;
                $unitAttributeId = $defaultAttributes[$showroomMapping->map_to] ?? null;

                foreach ($unit['attributes'] ?? [] as $key => $unitAttribute) {
                    if (($unitAttribute['attribute_id'] ?? null) != $unitAttributeId) {
                        continue;
                    }

                    $currentAttributeKey = $key;
                    break;
                }

                if ($currentAttributeKey !== null && !in_array($showroomMapping->map_to, $rewritableFields)) {
                    continue;
                }

                if ($currentAttributeKey !== null && in_array($showroomMapping->map_to, $rewritableFields)) {
                    // rewrite existing attribute
                    $unit['attributes'][$currentAttributeKey]['value'] = $showroom->{$showroomMapping->map_from};
                } else {
                    $attributes[$showroomMapping->map_to] = $showroom->{$showroomMapping->map_from};
                }

            } else {
                if (!empty($unit[$showroomMapping->map_to]) && !in_array($showroomMapping->map_to, $rewritableFields)) {
                    continue;
                }

                $unit[$showroomMapping->map_to] = $showroom->{$showroomMapping->map_from};
            }
        }

        $unit['length_display_mode'] = strpos($showroom->length_max, 'in') !== false ? 'inches' : 'feet';
        $unit['width_display_mode'] = strpos($showroom->width_max_real, 'in') !== false ? 'inches' : 'feet';

        $unit['payload_capacity'] = preg_replace('/[^0-9]/', '', $showroom->payload_capacity);

        if (!empty($showroom->length_max)) {
            $unit['length_inches'] = $this->convertHelper->fromFeetAndInches($showroom->length_max, ConvertHelper::DISPLAY_MODE_INCHES_ONLY, ConvertHelper::TYPE_LENGTH) ?? 0;
            $unit['length'] = $this->convertHelper->fromFeetAndInches($showroom->length_max, ConvertHelper::DISPLAY_MODE_FEET_ONLY, ConvertHelper::TYPE_LENGTH) ?? 0;
        }

        if (!empty($showroom->width_max_real)) {
            $unit['width_inches'] = $this->convertHelper->fromFeetAndInches($showroom->width_max_real, ConvertHelper::DISPLAY_MODE_INCHES_ONLY, ConvertHelper::TYPE_WIDTH) ?? 0;
            $unit['width'] = $this->convertHelper->fromFeetAndInches($showroom->width_max_real, ConvertHelper::DISPLAY_MODE_FEET_ONLY, ConvertHelper::TYPE_WIDTH) ?? 0;
        }

        if (!empty($showroom->video_embed_code)) {
            if (!empty($unit['video_embed_code'])) {
                $unit['video_embed_code'] .= "\n<!-- !video -->\n" . $showroom->video_embed_code;
            } else {
                $unit['video_embed_code'] = "\n<!-- !video -->\n" . $showroom->video_embed_code;
            }
        }

        $features = $this->getShowroomFeatures($showroom);
        $files = $this->getShowroomFiles($showroom);
        $images = $this->getShowroomImages($showroom);
        $inventoryAttributes = $this->getInventoryAttributes($attributes, $defaultAttributes);

        $unit['has_stock_images'] = empty($unit['images']) ? $this->hasStockImages : false;

        $unit['new_images'] = array_merge($unit['new_images'] ?? [], $images);
        $unit['new_files'] = array_merge($unit['new_files'] ?? [], $files);
        $unit['features'] = array_merge($unit['features'] ?? [], $features);
        $unit['attributes'] = array_merge($unit['attributes'] ?? [], $inventoryAttributes);

        // remove duplicates
        $unit['attributes'] = array_intersect_key($unit['attributes'], array_unique(array_map(function ($attribute) {
            return $attribute['attribute_id'];
        }, $unit['attributes'])));

        return $unit;
    }

    /**
     * @param array $unit
     * @param array $additionalSearchParams
     * @return Showroom|null
     */
    protected function getShowroomByUnit(array $unit, array $additionalSearchParams): ?Showroom
    {
        if (!isset($unit['year']) || !isset($unit['manufacturer']) || !isset($unit['model'])) {
            throw new ShowroomException('Some params are absent. Unit - ' . json_encode($unit));
        }

        $searchParams = ['external_mfg_key' => "{$unit['year']};{$unit['manufacturer']};{$unit['model']}"];

        /** @var Collection $showroomGenericMaps */
        $showroomGenericMaps = $this->showroomGenericMapRepository->getAll($searchParams);

        if ($showroomGenericMaps->isEmpty() && isset($unit['brand'])) {
            $searchParams = ['external_mfg_key' => "{$unit['year']};{$unit['manufacturer']};{$unit['model']};{$unit['brand']}"];
            /** @var Collection $showroomGenericMaps */
            $showroomGenericMaps = $this->showroomGenericMapRepository->getAll($searchParams);
        }

        if ($showroomGenericMaps->isEmpty()) {
            $searchParams = [
                'year' => $unit['year'],
                'model' => $unit['model'],
                'manufacturer' => $unit['manufacturer']
            ];

            /** @var Collection $showrooms */
            $showrooms = $this->showroomRepository->getAll($searchParams);
        } else {
            $showrooms = $showroomGenericMaps->first()->showrooms;
        }

        if (($additionalSearchParams['search_by_model_part'] ?? false) && isset($additionalSearchParams['model_part']) && $showrooms->isEmpty()) {
            unset($searchParams['model']);
            $searchParams['like_model'] = '%' . $additionalSearchParams['model_part'] . '%';

            /** @var Collection $showrooms */
            $showrooms = $this->showroomRepository->getAll($searchParams);
        }

        if ($showrooms->isEmpty()) {
            return null;
        }

        return $showrooms->first();
    }

    /**
     * @param Showroom $showroom
     * @return array
     */
    protected function getShowroomFeatures(Showroom $showroom): array
    {
        /** @var Collection<ShowroomFeature> $showroomFeatures */
        $showroomFeatures = $this->showroomFeatureRepository->getAll(['showroom_id' => $showroom->id]);

        $features = [];

        foreach ($showroomFeatures as $showroomFeature) {
            $features[] = [
                'feature_list_id' => $showroomFeature->feature_list_id,
                'value' => $showroomFeature->value,
            ];
        }

        return $features;
    }

    /**
     * @param Showroom $showroom
     * @return array
     */
    private function getShowroomFiles(Showroom $showroom): array
    {
        $files = array();

        $showroomFiles = $this->showroomFileRepository->getAll(['showroom_id' => $showroom->id]);

        foreach($showroomFiles as $showroomFile) {
            if (strpos($showroomFile['src'], 'showroom-files') !== false) {
                if ($showroomFile['src'][0] != '/') {
                    $showroomFile['src'] = '/' . $showroomFile['src'];
                }
                $filesRet = $showroomFile['src'];
            } else {
                $filesRet = '/showroom-files/' . $showroomFile['src'];
            }

            $files[] = [
                'title' => $filesRet,
                'url'  => $this->getS3Url($filesRet),
                'is_active' => self::DEFAULT_FILE_ACTIVE
            ];
        }

        return $files;
    }

    /**
     * @param Showroom $showroom
     * @return array
     */
    private function getShowroomImages(Showroom $showroom): array
    {
        $showroomImages = $this->showroomImageRepository->getAll(['showroom_id' => $showroom->id]);

        $imagesRet = [];
        $position = 1;

        foreach ($showroomImages as $showroomImage) {
            $url = env('CDN_URL') . '/showroom-files/' . $showroomImage->src;

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            $image = [
                'url' => $url,
                'is_stock' => $showroomImage->has_stock_overlay,
                'position' => $position++,
            ];

            if ($showroomImage->is_floorplan) {
                $image['is_secondary'] = true;
            }

            if ($showroomImage->has_stock_overlay) {
                $this->hasStockImages = true;
            }

            $imagesRet[] = $image;
        }

        return $imagesRet;
    }

    /**
     * @param array $attributes
     * @param array $defaultAttributes
     * @return array
     */
    protected function getInventoryAttributes(array $attributes, array $defaultAttributes): array
    {
        $inventoryAttributes = [];

        foreach ($attributes as $name => $value) {
            if (!isset($defaultAttributes[$name])) {
                continue;
            }

            $inventoryAttributes[] = [
                'attribute_id' => $defaultAttributes[$name],
                'value' => $value,
            ];
        }

        return $inventoryAttributes;
    }
}
