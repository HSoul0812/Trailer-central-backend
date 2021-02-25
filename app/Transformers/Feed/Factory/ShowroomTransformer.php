<?php

namespace App\Transformers\Feed\Factory;

use App\Helpers\ConvertHelper;
use App\Models\Showroom\ShowroomFieldsMapping;
use App\Repositories\Showroom\ShowroomFieldsMappingRepository;
use Dingo\Api\Http\Request;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;
use App\Models\Showroom\Showroom;

/**
 * Class ShowroomTransformer
 * @package App\Transformers\Feed\Factory
 */
class ShowroomTransformer extends TransformerAbstract
{
    /**
     * @var ShowroomFieldsMappingRepository
     */
    private $showroomFieldsMappingRepository;

    /**
     * @var ConvertHelper
     */
    private $convertHelper;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Collection<ShowroomFieldsMapping>
     */
    private $mapping;

    /**
     * ShowroomTransformer constructor.
     * @param ShowroomFieldsMappingRepository $showroomFieldsMappingRepository
     * @param ConvertHelper $convertHelper
     * @param Request $request
     */
    public function __construct(
        ShowroomFieldsMappingRepository $showroomFieldsMappingRepository,
        ConvertHelper $convertHelper,
        Request $request
    ) {
        $this->showroomFieldsMappingRepository = $showroomFieldsMappingRepository;
        $this->convertHelper = $convertHelper;
        $this->request = $request;
    }

    /**
     * @param Showroom $showroom
     * @return array
     */
    public function transform(Showroom $showroom): array
    {
        $data = [];

        if ($this->mapping === null) {
            $this->mapping = $this->showroomFieldsMappingRepository->getAll([]);
        }

        $select = $this->request->get('select');

        /** @var ShowroomFieldsMapping $map */
        foreach ($this->mapping as $map) {
            $mapFromArray = explode(',', $map->map_from);
            $value = '';

            foreach ($mapFromArray as $mapFrom) {
                /** If the field value is null or it does not exist, we don't add this field to response. */
                if (!isset($showroom->{$mapFrom}) && count($mapFromArray) === 1) {
                    continue 2;
                }

                /** To optimizing db queries, we never try to get a field value if it's absent in the "select" request param. */
                if (is_array($select) && !in_array($mapFrom, $select)) {
                    continue 2;
                }

                $value .= ' ' . (string)$showroom->{$mapFrom};
            }

            $value = trim($value);

            if ($map->field_type === ShowroomFieldsMapping::FIELD_TYPE_BOOLEAN) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }

            if ($map->field_type === ShowroomFieldsMapping::FIELD_TYPE_INTEGER) {
                $value = (int)$value;
            }

            switch ($map->type) {
                case ShowroomFieldsMapping::TYPE_INVENTORY:
                    $data[$map->map_to] = $value;
                    break;

                case ShowroomFieldsMapping::TYPE_ATTRIBUTE:
                    $data['attributes'][$map->map_to] = $value;
                    break;

                case ShowroomFieldsMapping::TYPE_MEASURE:
                    if (empty($data[$map->map_to])) {
                        $data[$map->map_to] = number_format(0, 2);
                        $data[$map->map_to . '_inches'] = number_format(0, 2);
                        $data[$map->map_to . '_second'] = number_format(0, 0);
                        $data[$map->map_to . '_second_inches'] = number_format(0, 0);
                    }

                    if (!empty($value)) {
                        $helper = $this->convertHelper;

                        $ftDec = $helper->fromFeetAndInches($value, ConvertHelper::DISPLAY_MODE_FEET, $map->map_to);
                        $inDec = $helper->fromFeetAndInches($value, ConvertHelper::DISPLAY_MODE_INCHES, $map->map_to);
                        $ftOnly = $helper->fromFeetAndInches($value, ConvertHelper::DISPLAY_MODE_FEET_INCHES_FEET_ONLY, $map->map_to);
                        $inOnly = $helper->fromFeetAndInches($value, ConvertHelper::DISPLAY_MODE_FEET_INCHES_INCHES_ONLY, $map->map_to);

                        // Format Feet/Inches
                        $return[$map->map_to] = number_format($ftDec, 2);
                        $return[$map->map_to . '_inches'] = number_format($inDec, 2);
                        $return[$map->map_to . '_second'] = number_format($ftOnly, 0);
                        $return[$map->map_to . '_second_inches'] = number_format($inOnly, 0);
                    }
                    break;

                default:
                    throw new \InvalidArgumentException('Wrong showroom fields mapping type. Class - ' . self::class);
            }
        }

        $with = $this->request->get('with', []);

        if (in_array('features', $with)) {
            $featuresList = [];

            foreach ($showroom->features as $feature) {
                $list = $feature->feature_list_id;

                if (!isset($featuresList[$list])) {
                    $featuresList[$list] = [];
                }

                $featuresList[$list][] = $feature->pivot->value;
            }

            $data['features'] = $featuresList;
        }

        return $data;
    }
}
