<?php

namespace App\Services\Integration\Transaction\Adapter\Pj;

use App\Models\User\User;
use App\Services\Integration\Transaction\Adapter\PjAdapter;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Class Inventory
 * @package App\Services\Integration\Transaction\Adapter\Pj
 */
class Inventory extends PjAdapter
{
    private const TITLE_FORMAT = '%d PJ Trailers %s Trailer';
    private const NOTES_FORMAT = '-- Auto imported %s --';

    private const ENTITY_TYPE = 1;

    protected $entityType = 'inventory';

    /**
     * @param $data
     * @return bool
     * @throws BindingResolutionException
     */
    public function add($data): bool
    {
        /*
        Hacky fix for PJ's broken inventory source file for some rules
        stock is generated on fly
        manufacturer always is PJ Trailers
        and year is set from source on Integrations
        so the last one is model and is required here.
        keep output above to be able to debug
        */
        if (empty($data['model'])) {
            return false;
        }

        $dealerID = $this->getEntityFromReference('dealer', $data['dealer_identifier']);
        $locationID = $this->getEntityFromReference('dealer_location', $data['location_identifier']);

        /** @var User $dealer */
        $dealer = $this->userRepository->get(['dealer_id' => $dealerID]);

        $inventoryParams = [
            'entity_type_id' => self::ENTITY_TYPE,
            'dealer_id' => $dealerID,
            'dealer_location_id' => $locationID,
            'active' => 1,
            'title' => sprintf(self::TITLE_FORMAT, $data['year'], $data['model']),
            'manufacturer' => 'PJ Trailers',
            'price' => 0,
            'model' => $data['model'],
            'notes' => sprintf(self::NOTES_FORMAT, date("F j, Y g:i a")),
            'category' => $data['category'],
            'vin' => $data['vin'],
            'year' => $data['year'],
            'condition' => 'new',
            'length' => $data['length'],
            'gvwr' => $data['gvwr'],
            'axle_capacity' => $data['axle_capacity'],
            'status' => $this->convert('status', $data['status'])
        ];

        $defaultDescription = $dealer->use_description_in_feed ? $dealer->default_description : '';
        $defaultDescription .= "\n\n\n\nWhile we strive to represent our trailers with 100% accuracy - please call to confirm details of trailer.";

        if (isset($data['description'])) {
            $description = $data['description'] . $defaultDescription;
        } else {
            $description = !empty($defaultDescription) ? $defaultDescription : '&nbsp;';
        }

        $inventoryParams['description'] = preg_match('!!u', $description) ? utf8_encode($description) : $description;

        switch($dealer->import_config) {
            case "model+vin":
                $inventoryParams['stock'] = $data['model'] . $data['vin'];
                break;
            case "last 4 of vin":
                $inventoryParams['stock'] = substr($data['vin'], -4);
                break;
            default:
            case "model+last 7 of vin (default)":
                $inventoryParams['stock'] = $data['model'] . substr($data['vin'], -7);
                break;
        }

        if($dealer->auto_import_hide == 1) {
            $inventoryParams['show_on_website'] = 0;
        } elseif($dealer->auto_import_hide == 2) {
            $inventoryParams['is_archived'] = 1;
            $inventoryParams['archived_at'] = (new \DateTime())->format('Y-m-d H:i:s');
        }

        if(isset($data['msrp'])) {
            $inventoryParams['msrp'] = $data['msrp'];
        }
        if(isset($data['width'])) {
            $inventoryParams['width'] = $data['width'];
        }
        if(isset($data['height'])) {
            $inventoryParams['height'] = $data['height'];
        }

        $inventoryParams['attributes'] = $this->getAttributes($data);

        $images = $data['image_urls'] ?? '';
        $images = is_string($images) ? explode(',', $images) : $images;

        foreach($images as $image) {
            if(strpos($image, 'http://') === false && substr($image, 0, 2) == '//') {
                $image = 'http:' . $image;
            }

            $inventoryParams['new_images'][] = ['url' => $image];
        }

        $inventory = $this->inventoryService->create($inventoryParams);

        $this->saveReference($inventory->inventory_id, $data['vin']);

        return true;
    }

    /**
     * @param $data
     * @return bool
     * @throws BindingResolutionException
     */
    public function update($data): bool
    {
        $id = $this->getEntityFromReference('inventory', $data['vin']);
        /** @var \App\Models\Inventory\Inventory $inventory */
        $inventory = $this->inventoryRepository->get(['id' => $id]);

        $inventoryParams = [];

        if(isset($data['location_identifier'])) {
            $inventoryParams['dealer_location_id'] = $this->getEntityFromReference('dealer_location', $data['location_identifier']);
        }

        $title = $data['year'] ?? $inventory->year;
        $title .= ' ' . $inventory->manufacturer;
        $title .= ' ' . ($data['model'] ?? $inventory->model);
        $title .= ' Trailer';

        $inventoryParams['title'] = $title;

        if(isset($data['model'])) {
            $inventoryParams['model'] = $data['model'];
        }
        if(isset($data['category'])) {
            $inventoryParams['category'] = $data['category'];
        }
        if(isset($data['msrp'])) {
            $inventoryParams['msrp'] = $data['msrp'];
        }
        if(isset($data['year'])) {
            $inventoryParams['year'] = $data['year'];
        }
        if(isset($data['length'])) {
            $inventoryParams['length'] = $data['length'];
        }
        if(isset($data['width'])) {
            $inventoryParams['width'] = $data['width'];
        }
        if(isset($data['height'])) {
            $inventoryParams['height'] = $data['height'];
        }
        if(isset($data['gvwr'])) {
            $inventoryParams['gvwr'] = $data['gvwr'];
        }
        if(isset($data['axle_capacity'])) {
            $inventoryParams['axle_capacity'] = $data['axle_capacity'];
        }
        if(isset($data['status'])) {
            $inventoryParams['status'] = $this->convert('status', $data['status']);
        }
        if(isset($data['description'])) {
            $inventoryParams['description'] = $data['description'];
        }

        $inventoryParams['attributes'] = $this->getAttributes($data);
        $inventoryParams['update_attributes'] = true;

        try {
            $this->inventoryService->update($inventoryParams);
        } catch(\Exception $e) {
            return false;
        }

        return true;

    }

    /**
     * @param $data
     * @return bool
     * @throws BindingResolutionException
     */
    public function delete($data): bool
    {
        $id = $this->getEntityFromReference('inventory', $data['vin']);

        return $this->inventoryService->delete($id);
    }

    /**
     * @param array $data
     * @return array
     */
    private function getAttributes(array $data): array
    {
        $attributes = array();

        if(isset($data['axles'])) {
            $attributes['axles'] = $data['axles'];
        }

        if(!empty($data['color']) && is_string($data['color'])) {
            $attributes['color'] = strtolower($this->convert('color', $data['color']));
        }

        if(!empty($data['hitch_type']) && is_string($data['hitch_type'])) {
            $attributes['pull_type'] = $this->convert('pull_type', $data['hitch_type']);
        }

        if(!empty($data['roof_type']) && is_string($data['roof_type'])) {
            $attributes['roof_type'] = $this->convert('roof_type', $data['roof_type']);
        }

        if(!empty($data['nose_type']) && is_string($data['nose_type'])) {
            $attributes['nose_type'] = $this->convert('nose_type', $data['nose_type']);
        }

        return $this->getInventoryAttributes(self::ENTITY_TYPE, $attributes);
    }
}
