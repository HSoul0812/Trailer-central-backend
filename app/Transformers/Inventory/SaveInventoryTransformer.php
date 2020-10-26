<?php

namespace App\Transformers\Inventory;

use App\Helpers\ConvertHelper;
use App\Helpers\SanitizeHelper;
use App\Repositories\Inventory\AttributeRepositoryInterface;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

/**
 * Class SaveInventoryTransformer
 * @package App\Transformers\Inventory
 */
class SaveInventoryTransformer extends TransformerAbstract
{
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

    private const IMAGES_FIELDS = [
        'new_images'
    ];

    private const IMAGE_FIELDS_MAPPING = [
        'secondary' => 'is_secondary',
        'primary' => 'is_default',
    ];

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
     * SaveInventoryTransformer constructor.
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ConvertHelper $convertHelper
     * @param SanitizeHelper $sanitizeHelper
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository, ConvertHelper $convertHelper, SanitizeHelper $sanitizeHelper)
    {
        $this->attributeRepository = $attributeRepository;

        $this->convertHelper = $convertHelper;
        $this->sanitizeHelper = $sanitizeHelper;
    }

    /**
     * @param array $params
     * @return array
     */
    public function transform(array $params): ?array
    {
        try {
            $convertHelper = $this->convertHelper;
            $sanitizeHelper = $this->sanitizeHelper;

            $defaultAttributes = $this->attributeRepository
                ->getAllByEntityTypeId($params['entity_type_id'])
                ->pluck('code', 'attribute_id')
                ->toArray();

            $createParams = $params;
            $attributes = [];
            $features = [];

            foreach ($createParams as $key => $value) {
                if (is_array($value)) {
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
                if (!empty($params[$embedField]) && is_array($params[$embedField])) {
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

                    foreach ($createParamValue as $value) {
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

            foreach (self::IMAGES_FIELDS as $imagesField) {
                if (!isset($params[$imagesField])) {
                    continue;
                }

                if ($imagesField === 'new_images') {
                    
                }

                foreach ($params[$imagesField] as $imageKey => $image) {
                    foreach (self::IMAGE_FIELDS_MAPPING as $paramsImageField => $modelImageField) {
                        if (isset($params[$imagesField][$imageKey][$modelImageField]) || !isset($params[$imagesField][$imageKey][$paramsImageField])) {
                            continue;
                        }

                        $createParams[$imagesField][$imageKey][$modelImageField] = $params[$imagesField][$imageKey][$paramsImageField];
                        unset($createParams[$imagesField][$imageKey][$paramsImageField]);
                    }
                }
            }

            return $createParams;
        } catch (\Exception $e) {
            Log::error('Item transform error.', $e->getTrace());
            return null;
        }
    }
}
