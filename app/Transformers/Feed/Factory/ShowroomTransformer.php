<?php

namespace App\Transformers\Feed\Factory;

use App\Helpers\ConvertHelper;
use App\Models\Showroom\ShowroomFieldsMapping;
use App\Repositories\Showroom\ShowroomFieldsMappingRepositoryInterface;
use Dingo\Api\Http\Request;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;
use App\Models\Showroom\Showroom;
use Markdownify\Converter;

/**
 * Class ShowroomTransformer
 * @package App\Transformers\Feed\Factory
 */
class ShowroomTransformer extends TransformerAbstract
{
    /**
     * @var ShowroomFieldsMappingRepositoryInterface
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
     * @var array
     */
    private $tcWwwLwhMapping;

    /**
     * @var array
     */
    private $tcWwwTypeLwh;

    /**
     * ShowroomTransformer constructor.
     * @param ShowroomFieldsMappingRepositoryInterface $showroomFieldsMappingRepository
     * @param ConvertHelper $convertHelper
     * @param Request $request
     */
    public function __construct(
        ShowroomFieldsMappingRepositoryInterface $showroomFieldsMappingRepository,
        ConvertHelper $convertHelper,
        Request $request
    ) {
        $this->showroomFieldsMappingRepository = $showroomFieldsMappingRepository;
        $this->convertHelper = $convertHelper;
        $this->request = $request;

        // These 2 variables are copied from tc-www
        $this->tcWwwLwhMapping = [
            'length' => ['length', 'length_min_real', 'length_min'],
            'width' => ['width', 'width_max_real', 'max_width', 'beam'],
            'height' => ['height', 'height_max_real', 'max_height'],
            'overall_length' => ['length_max_real', 'length_max'],
            'min_width' => ['width_min_real', 'min_width'],
            'min_height' => ['height_min_real', 'min_height']
        ];
        $this->tcWwwTypeLwh = [
            'length' => 'length',
            'overall_length' => 'length',
            'width' => 'width',
            'min_width' => 'width',
            'height' => 'height',
            'min_height' => 'height'
        ];
    }

    /**
     * @param Showroom $showroom
     * @return array
     *
     * @throws \InvalidArgumentException when it was inpossible to map some showroom fields mapping type
     */
    public function transform(Showroom $showroom): array
    {
        $data = [];
        $showroomFilesUrl = config('app.showroom_files_url');
        $helper = $this->convertHelper;

        if ($this->mapping === null) {
            $this->mapping = $this->showroomFieldsMappingRepository->getAll([]);
        }

        $data['showroom_id'] = $showroom->id;
        $data['brand'] = $showroom->brand;
        $data['series'] = $showroom->series;

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

                case ShowroomFieldsMapping::TYPE_IMAGE:
                    foreach ($showroom->{$map->map_from} as $image) {
                        $data[$map->map_to][]['url'] = $showroomFilesUrl . $image->src;
                    }
                    break;

                // Even though we added new code below, we don't want to remove  this code
                // just in case some columns has measure type, this way they won't break
                case ShowroomFieldsMapping::TYPE_MEASURE:
                    // No need to process if map to is in one of the tc-www mapping
                    // the replicate code below will deal with them manually
                    if (array_key_exists($map->map_to, $this->tcWwwLwhMapping)) {
                        break;
                    }

                    if (empty($data[$map->map_to])) {
                        $data[$map->map_to] = number_format(0, 2);
                        $data[$map->map_to . '_inches'] = number_format(0, 2);
                        $data[$map->map_to . '_second'] = number_format(0, 0);
                        $data[$map->map_to . '_second_inches'] = number_format(0, 0);
                    }

                    if (!empty($value)) {
                        $ftDec = $helper->fromFeetAndInches($value, ConvertHelper::DISPLAY_MODE_FEET, $map->map_to);
                        $inDec = $helper->fromFeetAndInches($value, ConvertHelper::DISPLAY_MODE_INCHES, $map->map_to);
                        $ftOnly = $helper->fromFeetAndInches($value, ConvertHelper::DISPLAY_MODE_FEET_INCHES_FEET_ONLY, $map->map_to);
                        $inOnly = $helper->fromFeetAndInches($value, ConvertHelper::DISPLAY_MODE_FEET_INCHES_INCHES_ONLY, $map->map_to);

                        // Format Feet/Inches
                        $data[$map->map_to] = number_format($ftDec, 2);
                        $data[$map->map_to . '_inches'] = number_format($inDec, 2);
                        $data[$map->map_to . '_second'] = number_format($ftOnly, 0);
                        $data[$map->map_to . '_second_inches'] = number_format($inOnly, 0);
                    }

                    break;
                default:
                    throw new \InvalidArgumentException("Wrong showroom fields mapping type ({$map->type}). Class - " . self::class);
            }
        }

        // We will add a measurement logic that we copied from tc-www here
        foreach ($this->tcWwwLwhMapping as $mapTo => $columns) {
            $type = $this->tcWwwTypeLwh[$mapTo];
            foreach ($columns as $column) {
                // Process this if only if the length exist in the row and if it's not empty or null
                if (!empty($showroom->{$column})) {
                    // Convert From Feet/Inches?
                    $v = $showroom->{$column}; // if length exists in the current row, keep it in v
                    $ftDec = $helper->fromFeetAndInches($v, ConvertHelper::DISPLAY_MODE_FEET, $type);
                    $inDec = $helper->fromFeetAndInches($v, ConvertHelper::DISPLAY_MODE_INCHES, $type);
                    $ftOnly = $helper->fromFeetAndInches($v, ConvertHelper::DISPLAY_MODE_FEET_INCHES_FEET_ONLY, $type);
                    $inOnly = $helper->fromFeetAndInches($v, ConvertHelper::DISPLAY_MODE_FEET_INCHES_INCHES_ONLY, $type);

                    // Format Feet/Inches
                    $data[$mapTo] = number_format($ftDec, 2);
                    $data[$mapTo . '_inches'] = number_format($inDec, 2);
                    $data[$mapTo . '_second'] = number_format($ftOnly, 0);
                    $data[$mapTo . '_second_inches'] = number_format($inOnly, 0);
                }
            }

            // Doesn't Exist Yet? Set to 0
            if (empty($data[$mapTo])) {
                $data[$mapTo] = number_format(0, 2);
                $data[$mapTo . '_inches'] = number_format(0, 2);
                $data[$mapTo . '_second'] = number_format(0, 0);
                $data[$mapTo . '_second_inches'] = number_format(0, 0);
            }
        }

        // For a pull type, we need to use tc-www logic
        if(in_array($showroom->type, ['camper_popup', 'tent-camper', 'toy', 'camping_rv', 'expandable', 'destination_trailer'])) {
            $data['attributes']['pull_type'] = 'bumper';
        } else if($showroom->type === 'fifth_wheel_campers') {
            $data['attributes']['pull_type'] = 'fifth_wheel';
        }

        // For a livingquarters attribute, we mark it as 1 automatically if the showroom type is camping_rv
        if ($showroom->type === 'camping_rv') {
            $data['attributes']['livingquarters'] = '1';
        }

        // Add the description_markdown
        if (isset($data['description'])) {
            // We need to remove the \r\n from the HTML string
            $description = str_replace("\r\n", "", $data['description']);
            $markdownDescription = (new Converter())->parseString($description);

            // Once converted to markdown, we need to replace some left-over
            // HTML line break with the \n
            $breaks = array("<br />","<br>","<br/>");
            $data['description_markdown'] = str_ireplace($breaks, "\n", $markdownDescription);
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
